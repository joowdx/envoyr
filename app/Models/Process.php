<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'document_id',
        'transmittal_id',
        'user_id',
        'office_id',
        'classification_id',
        'name',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function transmittal(): BelongsTo
    {
        return $this->belongsTo(Transmittal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    // Actions performed in this process (many-to-many with ActionType)
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(ActionType::class, 'process_actions', 'process_id', 'action_type_id')
            ->withPivot(['completed_at', 'completed_by', 'notes', 'sequence_order'])
            ->withTimestamps()
            ->orderBy('process_actions.sequence_order'); // Fixed: use table.column syntax
    }

    // Get available actions for this process based on office and classification
    public function getAvailableActions()
    {
        return ActionType::where('office_id', $this->office_id)
            ->where('is_active', true)
            ->whereNotIn('id', $this->actions()->pluck('action_types.id'))
            ->get();
    }

    // Check if process is complete (all required actions done)
    public function isComplete(): bool
    {
        $requiredActions = ActionType::where('office_id', $this->office_id)
            ->where('is_active', true)
            ->count();
        
        $completedActions = $this->actions()->whereNotNull('process_actions.completed_at')->count();
        
        return $completedActions >= $requiredActions;
    }

    // Get the workflow name based on classification and office
    public function getWorkflowName(): string
    {
        return ($this->classification->name ?? 'Unknown') . ' - ' . ($this->office->acronym ?? 'Unknown');
    }

    public static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->user_id = Auth::id();
            }
            
            // Auto-set classification from document if not provided (per flowchart)
            if (!$model->classification_id && $model->document_id) {
                $document = Document::find($model->document_id);
                if ($document) {
                    $model->classification_id = $document->classification_id;
                }
            }
        });
    }
}