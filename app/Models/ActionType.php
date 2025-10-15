<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActionType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'office_id',
        'name',
        'status_name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(OfficeAction::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            ActionType::class,
            'action_type_dependencies',
            'action_type_id',
            'prerequisite_action_type_id'
        )->withTimestamps();
    }

    public function dependentActions(): BelongsToMany
    {
        return $this->belongsToMany(
            ActionType::class,
            'action_type_dependencies',
            'prerequisite_action_type_id',
            'action_type_id'
        )->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($actionType) {
            Log::info('ActionType boot creating event triggered', [
                'name' => $actionType->name,
                'office_id' => $actionType->office_id,
                'status_name' => $actionType->status_name,
                'all_attributes' => $actionType->getAttributes()
            ]);
            
            if ($actionType->name) {
                $actionType->slug = Str::slug($actionType->name);
                Log::info('Generated slug: ' . $actionType->slug);
            } else {
                Log::warning('ActionType name is empty, cannot generate slug');
            }
        });

        static::updating(function ($actionType) {
            if ($actionType->isDirty('name') && $actionType->name) {
                $actionType->slug = Str::slug($actionType->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper: Check if specific action is a prerequisite
    public function hasPrerequisite(ActionType $actionType): bool
    {
        return $this->prerequisites()->where('action_types.id', $actionType->id)->exists();
    }

    // Helper: Check if all prerequisites are completed
    public function canBeExecuted(array $completedActionTypeIds): bool
    {
        $prerequisiteIds = $this->prerequisites()->pluck('action_types.id')->toArray();
        return empty(array_diff($prerequisiteIds, $completedActionTypeIds));
    }

    /**
     * Get all prerequisites recursively (including prerequisites of prerequisites)
     */
    public function getAllPrerequisites(): \Illuminate\Support\Collection
    {
        $allPrereqs = collect();
        $visited = collect();
        
        $this->collectPrerequisites($this, $allPrereqs, $visited);
        
        return $allPrereqs->unique('id');
    }

    private function collectPrerequisites(ActionType $action, \Illuminate\Support\Collection &$allPrereqs, \Illuminate\Support\Collection &$visited): void
    {
        if ($visited->contains('id', $action->id)) {
            return; // Prevent infinite recursion
        }
        
        $visited->push($action);
        
        foreach ($action->prerequisites as $prerequisite) {
            $allPrereqs->push($prerequisite);
            $this->collectPrerequisites($prerequisite, $allPrereqs, $visited);
        }
    }
}
