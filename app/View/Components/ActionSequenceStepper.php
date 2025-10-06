<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use App\Models\ActionType;
use App\Models\Office;

class ActionSequenceStepper extends Component
{
    public function __construct(
        public ?Office $office = null,
        public array $selectedActions = [],
        public array $availableActions = [],
        public bool $isModal = false,
        public ?string $onActionSelect = null,
        public ?string $onStepRemove = null,
        public ?string $onSequenceComplete = null
    ) {
        // If office is provided and no actions are loaded, fetch them
        if ($this->office && empty($this->availableActions)) {
            $this->availableActions = $this->office
                ->actionTypes()
                ->active()
                ->get()
                ->toArray();
        }
    }

    /**
     * Get available actions that can be added next
     */
    public function getAvailableNextActions(): array
    {
        if (empty($this->selectedActions)) {
            // If no actions selected, return all available actions
            return $this->availableActions;
        }

        $selectedActionIds = collect($this->selectedActions)->pluck('id')->toArray();
        
        return collect($this->availableActions)
            ->filter(function ($action) use ($selectedActionIds) {
                // Don't include already selected actions
                return !in_array($action['id'], $selectedActionIds);
            })
            ->values()
            ->toArray();
    }

    /**
     * Check if the sequence can be completed (has at least one action)
     */
    public function canCompleteSequence(): bool
    {
        return !empty($this->selectedActions);
    }

    /**
     * Get the current step number (1-indexed)
     */
    public function getCurrentStepNumber(): int
    {
        return count($this->selectedActions) + 1;
    }

    /**
     * Get stepper configuration for Alpine.js
     */
    public function getStepperConfig(): array
    {
        return [
            'selectedActions' => $this->selectedActions,
            'availableActions' => $this->getAvailableNextActions(),
            'currentStep' => $this->getCurrentStepNumber(),
            'canComplete' => $this->canCompleteSequence(),
            'callbacks' => [
                'onActionSelect' => $this->onActionSelect,
                'onStepRemove' => $this->onStepRemove,
                'onSequenceComplete' => $this->onSequenceComplete,
            ]
        ];
    }

    public function render(): View
    {
        return view('components.action-sequence-stepper');
    }
}