<?php

namespace App\Services\Workflow;

use App\Models\ActionType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;

class ProcessFormBuilder
{
    public function __construct(
        private ProcessActionFilter $actionFilter,
        private ProcessWorkflowValidator $validator,
        private string $officeId
    ) {}

    /**
     * Build the complete form schema for both create and edit
     */
    public function buildFormSchema(bool $isEdit = false): array
    {
        return [
            $this->buildProcessDetailsSection(),
            $this->buildWorkflowConfigurationSection($isEdit),
            $this->buildWorkflowPreviewSection(),
        ];
    }

    /**
     * Build the process details section
     */
    private function buildProcessDetailsSection(): Section
    {
        return Section::make('Process Details')
            ->description('Define the process workflow for document handling')
            ->schema([
                TextInput::make('name')
                    ->label('Process Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Budget Review Process')
                    ->helperText('Provide a clear, descriptive name for this process'),
                
                Select::make('classification_id')
                    ->label('Document Classification')
                    ->relationship('classification', 'name')
                    ->required()
                    ->placeholder('Select document classification')
                    ->helperText('Choose the type of documents this process will handle'),
            ])
            ->columns(2)
            ->columnSpan(2);
    }

    /**
     * Build the workflow configuration section
     */
    private function buildWorkflowConfigurationSection(bool $isEdit = false): Section
    {
        return Section::make('Workflow Configuration')
            ->description($isEdit ? 'Update the actions that will be performed in this process' : 'Configure the actions that will be performed in this process')
            ->schema([
                $this->buildActionTypeSelect($isEdit)
            ])
            ->columnSpan(2);
    }

    /**
     * Build the action type select component
     */
    private function buildActionTypeSelect(bool $isEdit = false): Select
    {
        $select = Select::make('action_type_id')
            ->label('Workflow Actions')
            ->multiple()
            ->placeholder('Select actions for this process workflow')
            ->getOptionLabelUsing(fn($value) => $this->actionFilter->getOptionLabel($value))
            ->helperText('Available actions for selection. Selected actions and those that would create circular dependencies are automatically hidden.')
            ->searchable()
            ->optionsLimit(50)
            ->noSearchResultsMessage('No actions found. Please create action types first.')
            ->minItems(1)
            ->maxItems(10)
            ->live()
            ->reactive()
            ->afterStateUpdated(function ($state, $set, $get) {
                $this->handleStateUpdate($state, $set, $get);
            });

        // Set options based on whether it's edit or create
        if ($isEdit) {
            $select->options(function ($state, $get, $record) {
                $currentState = $state ?? [];
                return $this->actionFilter->getFilteredActionOptions($record, $currentState);
            });
        } else {
            $select->options(function ($state, $get) {
                $currentState = $state ?? [];
                return $this->actionFilter->getFilteredActionOptions(null, $currentState);
            });
        }

        return $select;
    }

    /**
     * Build the workflow preview section
     */
    private function buildWorkflowPreviewSection(): Section
    {
        return Section::make('Workflow Preview')
            ->description('Visual representation of the configured workflow sequence')
            ->schema([
                ViewField::make('workflow_preview')
                    ->view('components.workflow-stepper')
                    ->viewData(function ($get) {
                        return $this->buildWorkflowPreviewData($get);
                    })
                    ->visible(fn ($get) => !empty($get('action_type_id'))),
            ])
            ->columnSpan(2);
    }

    /**
     * Simple state update handler
     */
    private function handleStateUpdate($state, $set, $get): void
    {
        // Clear previous state
        $set('stepper_data', []);
        $set('validation_error', null);
        $set('needs_reorder', false);
        $set('success_message', null);
        
        if (!empty($state)) {
            $results = $this->validator->getValidationResults($state);
            
            if ($results['valid']) {
                $set('stepper_data', $results['ordered_actions']);
                $set('needs_reorder', $results['needs_reorder']);
                $set('success_message', $results['message']);
            } else {
                $set('validation_error', $results['error']);
            }
        }
    }

    /**
     * Build simple workflow preview data
     */
    private function buildWorkflowPreviewData($get): array
    {
        $orderedActions = $get('stepper_data') ?? [];
        $validationError = $get('validation_error');
        $needsReorder = $get('needs_reorder') ?? false;
        $successMessage = $get('success_message');
        
        if ($validationError) {
            return [
                'selectedActions' => [],
                'actionTypes' => collect(),
                'validationError' => $validationError,
            ];
        }
        
        // Ensure ordered actions are properly formatted as scalars
        if (is_array($orderedActions)) {
            $orderedActions = array_map([$this, 'toStringId'], $orderedActions);
            // Remove null values
            $orderedActions = array_filter($orderedActions, fn($id) => $id !== null);
        } else {
            $orderedActions = [];
        }
        
        $actionTypes = ActionType::where('office_id', $this->officeId)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
        
        return [
            'selectedActions' => $orderedActions,
            'actionTypes' => $actionTypes,
            'needsReorder' => $needsReorder,
            'successMessage' => $successMessage,
            'actionCount' => count($orderedActions),
        ];
    }

    /**
     * Helper method to safely convert mixed data to string ID
     */
    private function toStringId($value): ?string
    {
        if (is_scalar($value)) {
            return (string) $value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        } elseif (is_object($value) && isset($value->id)) {
            return (string) $value->id;
        } elseif (is_array($value) && isset($value['id'])) {
            return (string) $value['id'];
        } else {
            return null;
        }
    }


}