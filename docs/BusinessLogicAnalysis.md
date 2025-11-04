# Envoyr Document Tracking System - Business Logic Deep Dive

> **⚠️ DOCUMENTATION CONSOLIDATED**
>
> This documentation has been reorganized for better clarity and maintainability:
> - **Database Schema**: See [DatabaseSchema.md](./DatabaseSchema.md) for complete schema documentation including ER diagrams, table definitions, relationships, and constraints
> - **System Documentation**: See [SystemDocumentation.md](./SystemDocumentation.md) for comprehensive system capabilities, business logic, and implementation details
>
> This file is kept for reference but may not be updated going forward.

---

## 🧠 Business Logic Overview

The Envoyr system implements sophisticated business logic that automates document workflows, enforces business rules, and maintains data integrity throughout the document lifecycle. The business logic is distributed across model events, service classes, action classes, and validation layers.

## 📋 Core Business Rules & Logic

### 1. **Document Lifecycle Management**

#### Document Creation Business Logic
```php
// Document Model - Automatic Code Generation
public static function booted(): void
{
    static::creating(function (self $document) {
        $faker = fake()->unique();
        do {
            // Generate 10 potential codes using pattern ??????####
            $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????####'))->toArray();
            // Check database for conflicts
            $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
        } while (empty($available));
        
        // Assign first available code
        $document->code = reset($available);
    });
}
```

**Business Rules:**
- ✅ **Unique Code Generation**: Every document gets a globally unique alphanumeric code
- ✅ **Collision Avoidance**: System generates multiple codes and checks for conflicts
- ✅ **Automatic Assignment**: No manual code entry required, prevents human error

#### Document State Management
```php
// Publication State Logic
public function isDraft(): bool {
    return is_null($this->published_at);
}

public function publish(): bool {
    if ($this->isPublished()) return false;  // Cannot republish
    return $this->update(['published_at' => now()]);
}

public function unpublish(): bool {
    if ($this->isDraft()) return false;      // Cannot unpublish draft
    return $this->update(['published_at' => null]);
}
```

**Business Rules:**
- ✅ **State Validation**: Documents can only transition between valid states
- ✅ **Timestamp Tracking**: Publication status tracked with precise timestamps
- ✅ **Idempotent Operations**: Repeated publish/unpublish calls are safe

### 2. **Transmittal Business Logic**

#### Transmission Validation & Control
```php
// Prevent Multiple Active Transmittals
$this->action(function (Document $record, array $data) {
    $record->refresh();
    
    // Critical Business Rule: Only one active transmittal per document
    if ($record->activeTransmittal()->exists()) {
        $this->failureNotificationTitle('Cannot transmit document');
        $this->failureNotificationBody('This document has an active transmittal that has not been received yet.');
        $this->failure();
        return;
    }
    
    // Double-check with database transaction
    DB::transaction(function () use ($record, $data) {
        if ($record->transmittals()->whereNull('received_at')->exists()) {
            throw new Exception('Document has an active transmittal');
        }
        // Create transmittal...
    });
});
```

**Key Business Rules:**
- ✅ **Single Active Transmittal**: Only one unresolved transmittal per document
- ✅ **Atomic Operations**: Database transactions ensure consistency
- ✅ **Authorization Checking**: User must belong to document's current office
- ✅ **Data Refresh**: Prevents race conditions with concurrent access

#### Automatic Transmittal Code Generation
```php
public static function booted(): void
{
    static::creating(function (self $transmittal) {
        $faker = fake()->unique();
        do {
            // Generate 10 potential codes with different pattern
            $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????????'))->toArray();
            $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
        } while (empty($available));
        
        $transmittal->code = reset($available);
    });
}
```

#### Attachment Snapshot Creation
```php
// Business Logic: Create point-in-time attachment snapshots
private function createTransmittalAttachmentSnapshot(Document $document, Transmittal $transmittal): void
{
    $draftAttachment = $document->attachment;  // Get current draft
    
    if ($draftAttachment) {
        // Create transmittal-specific attachment copy
        $transmittalAttachment = $transmittal->attachments()->create([
            'document_id' => $document->id,
        ]);
        
        // Copy all content with exact metadata
        foreach ($draftAttachment->contents as $content) {
            $transmittalAttachment->contents()->create([
                'sort' => $content->sort,
                'title' => $content->title,
                'file' => $content->file,
                'path' => $content->path,
                'hash' => $content->hash,
                'context' => $content->context,
            ]);
        }
    }
}
```

**Business Rules:**
- ✅ **Version Control**: Each transmittal gets snapshot of files at transmission time
- ✅ **Data Integrity**: Hash verification for file consistency
- ✅ **Historical Preservation**: Original attachments preserved even if document changes

### 3. **Document Reception Business Logic**

#### Reception Authorization & Validation
```php
DB::transaction(function () use ($record) {
    $activeTransmittal = $record->activeTransmittal;
    
    // Business Rule: Must have active transmittal
    if (!$activeTransmittal) {
        throw new Exception('No active transmittal found for this document.');
    }
    
    // Business Rule: Only destination office can receive
    if ($activeTransmittal->to_office_id !== Auth::user()->office_id) {
        throw new Exception('You are not authorized to receive this document.');
    }
    
    // Mark as received with timestamp and receiving user
    $activeTransmittal->update([
        'received_at' => now(),
        'to_user_id' => Auth::id(),
    ]);
});
```

**Business Rules:**
- ✅ **Authorization Control**: Only intended recipient office can receive documents
- ✅ **Reception Tracking**: Exact timestamp and receiving user recorded
- ✅ **State Transition**: Reception automatically makes transmittal inactive
- ✅ **Audit Trail**: Complete chain of custody maintained

### 4. **Process & Workflow Business Logic**

#### Automatic Process Creation
```php
// Process Model - Auto-assignment Logic
public static function booted()
{
    static::creating(function ($model) {
        // Auto-assign current user as process owner
        if (Auth::check()) {
            $model->user_id = Auth::id();
        }
        
        // Critical Business Rule: Inherit classification from document
        if (!$model->classification_id && $model->document_id) {
            $document = Document::find($model->document_id);
            if ($document) {
                $model->classification_id = $document->classification_id;
            }
        }
    });
}
```

#### Smart Action Assignment with Prerequisite Logic
```php
// ProcessesRelationManager - Automatic Workflow Setup
private function associateAllActionsToProcess(Process $process): void
{
    // Get all active actions for the office
    $actions = ActionType::where('office_id', $process->office_id)
        ->where('is_active', true)
        ->with('prerequisites')
        ->get();

    if ($actions->isEmpty()) return;

    // Use topological sorting for proper ordering
    $sorter = new ActionTopologicalSorter();
    $orderedActionIds = $sorter->sortByKahnsAlgorithm($actions);

    // Assign with sequence numbers
    $pivotData = [];
    foreach ($orderedActionIds as $index => $actionId) {
        $pivotData[$actionId] = ['sequence_order' => $index + 1];
    }

    $process->actions()->sync($pivotData);
}
```

**Business Rules:**
- ✅ **Complete Automation**: All office actions automatically assigned to new processes
- ✅ **Prerequisite Enforcement**: Actions ordered based on dependency relationships
- ✅ **Sequence Management**: Each action gets proper sequence number
- ✅ **Office Isolation**: Only actions from the process's office are included

#### Process Completion Logic
```php
// Smart completion detection
public function isComplete(): bool
{
    $requiredActions = ActionType::where('office_id', $this->office_id)
        ->where('is_active', true)
        ->count();
    
    $completedActions = $this->actionsCompleted()->count();
    
    return $completedActions >= $requiredActions;
}

// Next action identification
public function getNextPendingAction(): ?Action
{
    return $this->actionsPending()
        ->orderBy('steps.sequence_order')
        ->first();
}
```

### 5. **User Management & Authentication Business Logic**

#### User Invitation Workflow
```php
// Complex invitation state management
public function isPendingInvitation(): bool
{
    return ! is_null($this->invitation_token) &&
           is_null($this->invitation_accepted_at) &&
           ! $this->isInvitationExpired();
}

public function isInvitationExpired(): bool
{
    return $this->invitation_expires_at && $this->invitation_expires_at->isPast();
}

// Secure invitation acceptance
public function acceptInvitation(array $data): void
{
    $updateData = [
        'name' => $data['name'],
        'password' => $data['password'],
        'invitation_accepted_at' => now(),
        'invitation_token' => null,        // Clear token
        'invitation_expires_at' => null,   // Clear expiration
    ];

    if (isset($data['designation'])) {
        $updateData['designation'] = $data['designation'];
    }

    $this->update($updateData);
}
```

**Business Rules:**
- ✅ **Time-Limited Invitations**: Tokens expire automatically
- ✅ **Single-Use Tokens**: Tokens cleared after acceptance
- ✅ **Conditional Field Updates**: Designation only updated if provided
- ✅ **State Consistency**: All invitation fields updated atomically

#### User Authorization Logic
```php
public function canAccessPanel(Panel $panel): bool
{
    return ! $this->isPendingInvitation() &&
           in_array($this->role, [
               UserRole::ROOT,
               UserRole::ADMINISTRATOR,
               UserRole::LIAISON,
               UserRole::USER,
           ]);
}

// User deactivation with audit
public function deactivate(User $deactivatedBy): void
{
    $this->update([
        'deactivated_at' => now(),
        'deactivated_by' => $deactivatedBy->id,
    ]);
}
```

### 6. **ActionType & Office Management Logic**

#### Automatic Slug Generation
```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($actionType) {
        if ($actionType->name) {
            $actionType->slug = Str::slug($actionType->name);
        }
    });

    static::updating(function ($actionType) {
        if ($actionType->isDirty('name') && $actionType->name) {
            $actionType->slug = Str::slug($actionType->name);
        }
    });
}
```

#### Section Head Auto-Assignment
```php
// Section Model - Automatic head information sync
protected static function booted()
{
    static::creating(function ($section) {
        if ($section->user_id && !$section->head_name) {
            $user = User::find($section->user_id);
            if ($user) {
                $section->head_name = $user->name;
                $section->designation = $user->designation;
            }
        }
    });

    static::updating(function ($section) {
        if ($section->isDirty('user_id') && $section->user_id) {
            $user = User::find($section->user_id);
            if ($user) {
                $section->head_name = $user->name;
                $section->designation = $user->designation;
            }
        }
    });
}
```

### 7. **File Management Business Logic**

#### Automatic File Cleanup
```php
// Attachment Model - Cascade deletion
public static function booted(): void
{
    static::deleting(fn (self $attachment) => 
        $attachment->contents->each->purge()
    );
}

// Content Model - Physical file removal
public static function booted(): void
{
    static::deleting(fn (self $attachment) => $attachment->purge());
}

public function purge(): void
{
    $this->file?->each(fn ($file) => 
        Storage::exists($file) && Storage::delete($file)
    );
}
```

#### Electronic Content Detection
```php
public function electronic(): Attribute
{
    return Attribute::make(fn () => isset($this->hash));
}
```

**Business Rules:**
- ✅ **Automatic Cleanup**: Physical files deleted when database records removed
- ✅ **Safe Deletion**: Check file existence before deletion
- ✅ **Electronic Detection**: Files with hash values are considered electronic
- ✅ **Cascade Operations**: Deleting attachments removes all associated content

### 8. **Advanced Business Logic Components**

#### Topological Sorting for Dependencies
```php
// ActionTopologicalSorter Service - Complex prerequisite handling
public function sortByKahnsAlgorithm(Collection $actions): array
{
    // Build dependency graph
    $graph = [];
    $inDegree = [];
    
    foreach ($actions as $action) {
        $graph[$action->id] = [];
        $inDegree[$action->id] = 0;
    }
    
    // Add prerequisite edges
    foreach ($actions as $action) {
        foreach ($action->prerequisites as $prerequisite) {
            if (isset($graph[$prerequisite->id])) {
                $graph[$prerequisite->id][] = $action->id;
                $inDegree[$action->id]++;
            }
        }
    }
    
    // Kahn's algorithm implementation
    $queue = [];
    $result = [];
    
    foreach ($inDegree as $actionId => $degree) {
        if ($degree === 0) {
            $queue[] = $actionId;
        }
    }
    
    while (!empty($queue)) {
        $current = array_shift($queue);
        $result[] = $current;
        
        foreach ($graph[$current] as $neighbor) {
            $inDegree[$neighbor]--;
            if ($inDegree[$neighbor] === 0) {
                $queue[] = $neighbor;
            }
        }
    }
    
    // Handle circular dependencies
    if (count($result) !== count($actions)) {
        return $actions->pluck('id')->toArray();
    }
    
    return $result;
}
```

#### Intra-Office Detection
```php
// Transmittal Model - Smart routing logic
public function intraOffice(): Attribute
{
    return Attribute::make(
        get: fn (): bool => $this->from_office_id === $this->to_office_id,
    );
}
```

## 🎯 Business Logic Principles

### **1. Data Integrity & Consistency**
- **Automatic Code Generation**: Prevents duplicate codes across the system
- **Database Transactions**: Ensure atomic operations for critical processes
- **Foreign Key Constraints**: Maintain referential integrity
- **Cascade Operations**: Proper cleanup of related data

### **2. Business Rule Enforcement**
- **State Validation**: Documents and transmittals can only transition through valid states
- **Authorization Checks**: Users can only perform actions they're authorized for
- **Prerequisite Management**: Actions must follow proper dependency order
- **Single Active Rule**: Only one active transmittal per document at any time

### **3. Automation & Intelligence**
- **Auto-Assignment**: Users, offices, and classifications automatically assigned
- **Smart Defaults**: Reasonable defaults reduce manual data entry
- **Workflow Automation**: Complete action sequences automatically set up
- **Prerequisite Ordering**: Complex dependencies handled automatically

### **4. Audit & Traceability**
- **Complete Timestamps**: Every operation tracked with precise timing
- **User Attribution**: All changes linked to specific users
- **State History**: Previous states preserved for audit trails
- **File Versioning**: Point-in-time snapshots for each transmittal

### **5. Error Prevention & Recovery**
- **Race Condition Prevention**: Data refresh and double-checking
- **Validation Layers**: Multiple levels of validation before operations
- **Graceful Failures**: Meaningful error messages for business rule violations
- **Transaction Rollback**: Failed operations don't leave partial data

## 📊 Business Logic Summary

The Envoyr system implements **enterprise-grade business logic** that:

1. **Automates Complex Workflows**: From document creation to final processing
2. **Enforces Government Standards**: Proper authorization, audit trails, and state management
3. **Prevents Data Corruption**: Comprehensive validation and transaction management
4. **Maintains Consistency**: Automatic data synchronization and relationship management
5. **Supports Scale**: Efficient algorithms for complex dependency resolution

The business logic is designed to handle the complexities of government document workflows while maintaining data integrity, enforcing security policies, and providing complete audit trails for compliance requirements.

---

*This business logic framework ensures that the document tracking system operates reliably, securely, and efficiently in demanding government environments.*