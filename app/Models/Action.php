<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Action extends Model
{
    use HasUlids, SoftDeletes;

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

    // Direct relationship to office (no intermediate OfficeAction table)
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    // Self-referencing: Actions this action requires (prerequisites)
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            Action::class,
            'prerequisites',
            'action_id',
            'required_action_id'
        )->withTimestamps();
    }

    // Self-referencing: Actions that require this action (dependents)
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Action::class,
            'prerequisites',
            'required_action_id',
            'action_id'
        )->withTimestamps();
    }

    // Process relationship
    public function processes(): BelongsToMany
    {
        return $this->belongsToMany(Process::class, 'steps')
            ->withPivot('sequence_order', 'completed_at', 'completed_by', 'notes')
            ->withTimestamps();
    }

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($action) {
            Log::info('Action boot creating event triggered', [
                'name' => $action->name,
                'office_id' => $action->office_id,
                'status_name' => $action->status_name,
                'all_attributes' => $action->getAttributes(),
            ]);

            if ($action->name) {
                $action->slug = Str::slug($action->name);
                Log::info('Generated slug: '.$action->slug);
            } else {
                Log::warning('Action name is empty, cannot generate slug');
            }
        });

        static::updating(function ($action) {
            if ($action->isDirty('name') && $action->name) {
                $action->slug = Str::slug($action->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper: Check if specific action is a prerequisite
    public function hasPrerequisite(Action $action): bool
    {
        return $this->prerequisites()->where('actions.id', $action->id)->exists();
    }

    // Helper: Check if all prerequisites are completed
    public function canBeExecuted(array $completedActionIds): bool
    {
        $prerequisiteIds = $this->prerequisites()->pluck('actions.id')->toArray();

        return empty(array_diff($prerequisiteIds, $completedActionIds));
    }
}
