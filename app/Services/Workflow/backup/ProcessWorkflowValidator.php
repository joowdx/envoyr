<?php

namespace App\Services\Workflow;

use App\Models\ActionType;
use Illuminate\Database\Eloquent\Collection;

class ProcessWorkflowValidator
{
    /**
     * Validate workflow and reorder actions based on prerequisites
     */
    public function validateAndReorderActions(array $actionIds): array
    {
        if (empty($actionIds)) {
            return [
                'valid' => true,
                'ordered_actions' => [],
                'message' => 'No actions selected'
            ];
        }

        // Get actions with their prerequisites
        $actions = ActionType::whereIn('id', $actionIds)
            ->with('prerequisites')
            ->get()
            ->keyBy('id');

        // Check if all actions exist - ensure type consistency for comparison
        $actionIdsAsStrings = array_map('strval', $actionIds);
        $existingIdsAsStrings = $actions->keys()->map('strval')->toArray();
        $missingActions = array_diff($actionIdsAsStrings, $existingIdsAsStrings);
        if (!empty($missingActions)) {
            return [
                'valid' => false,
                'ordered_actions' => [],
                'message' => 'Some selected actions no longer exist: ' . implode(', ', $missingActions)
            ];
        }

        // Check for missing prerequisites and provide helpful suggestions
        $missingPrereqs = $this->checkMissingPrerequisites($actions, $actionIds);
        if (!empty($missingPrereqs)) {
            $suggestions = $this->generatePrerequisiteSuggestions($missingPrereqs);
            return [
                'valid' => false,
                'ordered_actions' => [],
                'message' => 'Missing required prerequisites. ' . $suggestions
            ];
        }

        // Perform topological sort
        $orderedActions = $this->simpleTopologicalSort($actionIds, $actions);
        
        if (empty($orderedActions)) {
            return [
                'valid' => false,
                'ordered_actions' => [],
                'message' => 'Cannot arrange actions due to circular dependencies'
            ];
        }

        $reorderMessage = null;
        if ($actionIds !== $orderedActions) {
            $reorderMessage = 'Actions were automatically reordered to respect prerequisites';
        }

        return [
            'valid' => true,
            'ordered_actions' => $orderedActions,
            'message' => 'Workflow is valid',
            'was_reordered' => $actionIds !== $orderedActions,
            'reorder_message' => $reorderMessage
        ];
    }

    /**
     * Check for missing prerequisites for the selected actions
     */
    private function checkMissingPrerequisites($actions, array $selectedIds): array
    {
        $missing = [];
        
        // Ensure selectedIds are strings for consistent comparison
        $selectedIds = array_map('strval', $selectedIds);
        
        foreach ($actions as $action) {
            $prerequisiteIds = $action->prerequisites->pluck('id')->map('strval')->toArray();
            $missingForThisAction = array_diff($prerequisiteIds, $selectedIds);
            
            if (!empty($missingForThisAction)) {
                $missing[(string) $action->id] = [
                    'action_name' => $action->name,
                    'missing_prerequisite_ids' => $missingForThisAction,
                    'missing_prerequisites' => $action->prerequisites->whereIn('id', $missingForThisAction)
                ];
            }
        }
        
        return $missing;
    }

    /**
     * Generate helpful suggestions for missing prerequisites
     */
    private function generatePrerequisiteSuggestions(array $missingPrereqs): string
    {
        $suggestions = [];
        
        foreach ($missingPrereqs as $actionId => $info) {
            $actionName = $info['action_name'];
            $missingNames = $info['missing_prerequisites']->pluck('name')->toArray();
            
            if (count($missingNames) === 1) {
                $suggestions[] = "'{$actionName}' requires '{$missingNames[0]}'";
            } else {
                $nameList = implode(', ', array_slice($missingNames, 0, -1)) . ' and ' . end($missingNames);
                $suggestions[] = "'{$actionName}' requires {$nameList}";
            }
        }
        
        $suggestionText = implode('; ', array_slice($suggestions, 0, 3));
        if (count($suggestions) > 3) {
            $suggestionText .= ' and ' . (count($suggestions) - 3) . ' more issues';
        }
        
        return $suggestionText . '. Please add the missing prerequisites or remove the dependent actions.';
    }

    /**
     * Get simple validation results
     */
    public function getValidationResults(array $actionIds): array
    {
        if (empty($actionIds)) {
            return [
                'valid' => false,
                'error' => 'Please select actions for your workflow.',
                'needs_reorder' => false,
            ];
        }

        try {
            $orderedActions = $this->validateAndReorderActions($actionIds);
            
            return [
                'valid' => true,
                'ordered_actions' => $orderedActions,
                'needs_reorder' => $actionIds !== $orderedActions,
                'message' => count($orderedActions) === 1 ? 
                    'Single action workflow ready.' : 
                    count($orderedActions) . ' actions configured successfully.',
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'needs_reorder' => false,
            ];
        }
    }

    /**
     * Find missing prerequisites - simple and clear
     */
    public function findMissingPrerequisites(array $actionIds, Collection $actionTypes): array
    {
        $missing = [];
        
        foreach ($actionIds as $actionId) {
            $action = $actionTypes->get($actionId);
            if (!$action) continue;
            
            foreach ($action->prerequisites as $prerequisite) {
                if (!in_array($prerequisite->id, $actionIds)) {
                    $missing[] = $prerequisite->name;
                }
            }
        }
        
        return array_unique($missing);
    }

    /**
     * Simple topological sort - clean and predictable
     */
    public function simpleTopologicalSort(array $actionIds, $actionTypes): array
    {
        if (empty($actionIds)) {
            return [];
        }

        // Ensure all IDs are strings for consistent array operations
        $actionIds = array_map('strval', $actionIds);
        
        $sorted = [];
        $visited = [];
        $temp = [];

        foreach ($actionIds as $actionId) {
            if (!in_array($actionId, $visited)) {
                if (!$this->topologicalSortHelper($actionId, $actionTypes, $visited, $temp, $sorted, $actionIds)) {
                    return []; // Circular dependency detected
                }
            }
        }

        return array_reverse($sorted);
    }

    private function topologicalSortHelper($actionId, $actionTypes, &$visited, &$temp, &$sorted, $allowedIds): bool
    {
        // Ensure actionId is a string/scalar for array operations
        $actionId = (string) $actionId;
        
        if (in_array($actionId, $temp)) {
            return false; // Circular dependency
        }

        if (in_array($actionId, $visited)) {
            return true; // Already processed
        }

        $temp[] = $actionId;
        
        // Process prerequisites first
        $action = null;
        if (is_a($actionTypes, \Illuminate\Support\Collection::class)) {
            $action = $actionTypes->get($actionId);
        } else {
            // Handle array case
            $action = isset($actionTypes[$actionId]) ? $actionTypes[$actionId] : null;
        }
        
        if ($action && $action->prerequisites) {
            foreach ($action->prerequisites as $prerequisite) {
                $prereqId = (string) $prerequisite->id;
                // Convert allowedIds elements to strings for comparison
                $allowedIdsStrings = array_map('strval', $allowedIds);
                if (in_array($prereqId, $allowedIdsStrings)) {
                    if (!$this->topologicalSortHelper($prereqId, $actionTypes, $visited, $temp, $sorted, $allowedIds)) {
                        return false;
                    }
                }
            }
        }

        $temp = array_diff($temp, [$actionId]);
        $visited[] = $actionId;
        $sorted[] = $actionId;

        return true;
    }
}