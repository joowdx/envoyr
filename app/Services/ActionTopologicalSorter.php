<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ActionTopologicalSorter
{
    /**
     * Sort actions using Kahn's algorithm for topological sorting.
     * This approach is suitable for collections of actions with prerequisites.
     *
     * @param  Collection  $actions  Collection of Action models with prerequisites relationship loaded
     * @return array Array of action IDs in topologically sorted order
     */
    public function sortByKahnsAlgorithm(Collection $actions): array
    {
        if ($actions->isEmpty()) {
            return [];
        }

        $graph = [];
        $inDegree = [];

        // Build dependency graph
        foreach ($actions as $action) {
            $graph[$action->id] = [];
            $inDegree[$action->id] = 0;
        }

        // Add edges for prerequisites
        foreach ($actions as $action) {
            foreach ($action->prerequisites as $prerequisite) {
                if (isset($graph[$prerequisite->id])) {
                    $graph[$prerequisite->id][] = $action->id;
                    $inDegree[$action->id]++;
                }
            }
        }

        // Kahn's algorithm for topological sort
        $queue = [];
        $result = [];

        // Start with actions that have no prerequisites
        foreach ($inDegree as $actionId => $degree) {
            if ($degree === 0) {
                $queue[] = $actionId;
            }
        }

        while (! empty($queue)) {
            $current = array_shift($queue);
            $result[] = $current;

            // Remove edges and update in-degrees
            foreach ($graph[$current] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        // If we couldn't order all actions, just return them as-is
        if (count($result) !== count($actions)) {
            return $actions->pluck('id')->toArray();
        }

        return $result;
    }

    /**
     * Sort actions using DFS-based topological sorting.
     * This approach is suitable when you have specific action IDs to sort from a larger collection.
     *
     * @param  array  $actionIds  Array of action IDs to sort
     * @param  Collection|array  $actionTypes  Collection or array of Action models with prerequisites
     * @return array Array of action IDs in topologically sorted order
     */
    public function sortByDepthFirstSearch(array $actionIds, $actionTypes): array
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
            if (! in_array($actionId, $visited)) {
                if (! $this->dfsTopologicalSortHelper($actionId, $actionTypes, $visited, $temp, $sorted, $actionIds)) {
                    // Circular dependency detected, return original order
                    return $actionIds;
                }
            }
        }

        return $sorted; // Don't reverse, we're adding in correct order now
    }

    /**
     * Helper method for DFS-based topological sorting
     */
    private function dfsTopologicalSortHelper($actionId, $actionTypes, &$visited, &$temp, &$sorted, $allowedIds): bool
    {
        // Ensure actionId is a string for consistent array operations
        $actionId = (string) $actionId;

        if (in_array($actionId, $temp)) {
            return false; // Circular dependency
        }

        if (in_array($actionId, $visited)) {
            return true; // Already processed
        }

        $temp[] = $actionId;

        // Get the action model
        $action = $this->getActionFromCollection($actionId, $actionTypes);

        // Process prerequisites first (this is the key for topological order)
        if ($action && $action->prerequisites) {
            foreach ($action->prerequisites as $prerequisite) {
                $prereqId = (string) $prerequisite->id;
                // Convert allowedIds elements to strings for comparison
                $allowedIdsStrings = array_map('strval', $allowedIds);
                if (in_array($prereqId, $allowedIdsStrings)) {
                    if (! $this->dfsTopologicalSortHelper($prereqId, $actionTypes, $visited, $temp, $sorted, $allowedIds)) {
                        return false;
                    }
                }
            }
        }

        // Remove from temp and add to visited and sorted AFTER processing prerequisites
        $temp = array_diff($temp, [$actionId]);
        $visited[] = $actionId;
        $sorted[] = $actionId; // Add to end (not reversed later)

        return true;
    }

    /**
     * Helper to get an action from either a Collection or array
     */
    private function getActionFromCollection($actionId, $actionTypes)
    {
        if (is_a($actionTypes, Collection::class)) {
            return $actionTypes->get($actionId);
        }

        // Handle array case
        return isset($actionTypes[$actionId]) ? $actionTypes[$actionId] : null;
    }

    /**
     * Convenience method that automatically chooses the best sorting algorithm
     * based on the input parameters.
     *
     * @param  Collection|array  $actions  Either a Collection of actions or array of action IDs
     * @param  Collection|array|null  $actionTypes  Optional collection/array of Action models (required if first param is array of IDs)
     * @return array Array of action IDs in topologically sorted order
     */
    public function sort($actions, $actionTypes = null): array
    {
        // If actions is a Collection, use Kahn's algorithm
        if (is_a($actions, Collection::class)) {
            return $this->sortByKahnsAlgorithm($actions);
        }

        // If actions is an array and we have actionTypes, use DFS
        if (is_array($actions) && $actionTypes !== null) {
            return $this->sortByDepthFirstSearch($actions, $actionTypes);
        }

        // Fallback: return as-is if we can't determine the appropriate method
        return is_array($actions) ? $actions : [];
    }
}
