<?php

namespace App\Services\Workflow;

use App\Models\ActionType;
use Illuminate\Database\Eloquent\Model;

class ProcessActionFilter
{
    public function __construct(
        private string $officeId
    ) {}

    /**
     * Get filtered action options excluding current selections and their descendants
     * to prevent circular dependencies when editing
     */
    public function getFilteredActionOptions(?Model $record = null, array $currentSelection = []): array
    {
        $query = ActionType::where('office_id', $this->officeId)
            ->where('is_active', true)
            ->with('prerequisites', 'dependentActions');

        // Get all actions
        $allActions = $query->get();
        
        // Determine which actions to exclude
        $excludedIds = collect();
        
        // If editing a process, get current action type IDs from the process
        $currentActionIds = [];
        if ($record) {
            // Get current action type IDs from the process
            $currentActionIds = $record->actions()->pluck('action_types.id')->toArray();
        }
        
        // Use current selection from form state if provided, otherwise use record data
        $activeActionIds = !empty($currentSelection) ? $currentSelection : $currentActionIds;
        
        // Exclude currently selected actions (they shouldn't appear in dropdown)
        $excludedIds = $excludedIds->merge($activeActionIds);
        
        // For each currently selected action, exclude its descendants to prevent circular dependencies
        foreach ($activeActionIds as $actionId) {
            $action = $allActions->firstWhere('id', $actionId);
            if ($action) {
                // Get all descendants (actions that depend on this action)
                $descendants = $action->getAllDependents();
                $excludedIds = $excludedIds->merge($descendants->pluck('id'));
            }
        }
        
        // Filter out excluded actions
        $availableActions = $allActions->whereNotIn('id', $excludedIds->unique()->toArray());
        
        return $availableActions->mapWithKeys(function ($action) {
            $label = $action->name;
            
            if ($action->prerequisites->isNotEmpty()) {
                $prereqNames = $action->prerequisites->pluck('name')->toArray();
                $prereqCount = count($prereqNames);
                
                if ($prereqCount === 1) {
                    $label .= " 🔗 Requires: " . $prereqNames[0];
                } else {
                    $label .= " 🔗 Requires: " . implode(', ', array_slice($prereqNames, 0, 2));
                    if ($prereqCount > 2) {
                        $label .= " + " . ($prereqCount - 2) . " more";
                    }
                }
            }
            
            // Ensure the key is a string to avoid array_key_exists issues
            return [(string) $action->id => $label];
        })->toArray();
    }

    /**
     * Get option label for selected values
     */
    public function getOptionLabel($value): string
    {
        $action = ActionType::where('office_id', $this->officeId)
            ->where('id', $value)
            ->first();
        
        return $action ? $action->name : "Action #{$value}";
    }

    /**
     * Get detailed information about an action's prerequisites for display
     */
    public function getActionPrerequisiteInfo($actionId): array
    {
        // Ensure actionId is properly formatted
        $actionId = (string) $actionId;
        
        $action = ActionType::where('office_id', $this->officeId)
            ->where('id', $actionId)
            ->with('prerequisites')
            ->first();
            
        if (!$action) {
            return ['has_prerequisites' => false, 'prerequisites' => [], 'warning' => null];
        }
        
        $prerequisites = $action->prerequisites;
        $info = [
            'has_prerequisites' => $prerequisites->isNotEmpty(),
            'prerequisites' => $prerequisites->map(fn($p) => [
                'id' => (string) $p->id,
                'name' => $p->name
            ])->toArray(),
            'warning' => null
        ];
        
        if ($prerequisites->isNotEmpty()) {
            $count = $prerequisites->count();
            if ($count === 1) {
                $info['warning'] = "This action requires '{$prerequisites->first()->name}' to be completed first.";
            } else {
                $names = $prerequisites->pluck('name')->join(', ', ' and ');
                $info['warning'] = "This action requires {$count} prerequisites: {$names}.";
            }
        }
        
        return $info;
    }
}