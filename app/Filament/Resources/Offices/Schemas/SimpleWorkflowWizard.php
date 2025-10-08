<?php
// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Schemas/SimpleWorkflowWizard.php

namespace App\Filament\Resources\Offices\Schemas;

use App\Models\ActionType;
use App\Models\Classification;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard\Step;

class SimpleWorkflowWizard
{
    public static function createSimpleWorkflow($ownerRecord): array
    {
        return [
            Wizard::make([
                self::basicInfoStep(),
                self::sequenceActionsStep($ownerRecord),
                self::previewStep($ownerRecord),
            ])
            ->skippable()
            ->persistStepInQueryString()
        ];
    }

    private static function basicInfoStep(): Step
    {
        return Step::make('Basic Information')
            ->description('Tell us about your document processing workflow')
            ->icon('heroicon-o-information-circle')
            ->schema([
                Grid::make(1)
                    ->schema([
                        Select::make('classification_id')
                            ->label('What type of documents will this workflow handle?')
                            ->options(Classification::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->placeholder('e.g., Payroll, Legal Documents, Budget')
                            ->helperText('Choose the document classification per ER diagram'),
                    ]),
                    
                TextInput::make('process_name')
                    ->label('Give your document process a name')
                    ->required()
                    ->placeholder('e.g., Monthly Payroll Processing Workflow')
                    ->helperText('Clear name following Document Tracking System patterns'),
                    
                Textarea::make('workflow_purpose')
                    ->label('What is the purpose of this document workflow?')
                    ->placeholder('Describe how documents flow through this process...')
                    ->rows(2)
                    ->helperText('Explain the document processing flow per flowchart requirements'),
            ]);
    }

    private static function sequenceActionsStep($ownerRecord): Step
    {
        return Step::make('Build Action Sequence')
            ->description('Select actions to add them to your workflow sequence')
            ->icon('heroicon-o-arrows-up-down')
            ->schema([
                // Action selector
                Select::make('action_to_add')
                    ->label('Add Action to Workflow')
                    ->options(function () use ($ownerRecord) {
                        return ActionType::where('office_id', $ownerRecord->id)
                            ->where('is_active', true)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->placeholder('Select an action to add to the workflow...')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state) {
                            // Get current sequence
                            $currentSequence = json_decode($get('action_sequence') ?? '[]', true);
                            
                            // Get the selected action details
                            $action = ActionType::find($state);
                            if ($action) {
                                // Add to sequence with step order
                                $stepOrder = count($currentSequence) + 1;
                                $currentSequence[] = [
                                    'id' => $action->id,
                                    'action_type_id' => $action->id,
                                    'name' => $action->name,
                                    'status_name' => $action->status_name,
                                    'step_order' => $stepOrder,
                                    'resulting_status' => $action->status_name,
                                    'action_name' => $action->name,
                                ];
                                
                                // Update hidden field
                                $set('action_sequence', json_encode($currentSequence));
                                
                                // Clear the selector
                                $set('action_to_add', null);
                            }
                        }
                    })
                    ->helperText('Choose an action to add it to your workflow sequence'),

                // Hidden field to store the sequence data
                Hidden::make('action_sequence')
                    ->default('[]'),
                
                // The dynamic stepper component
                ViewField::make('stepper')
                    ->view('components.action-sequence-stepper')
                    ->viewData(function ($get) use ($ownerRecord) {
                        $selectedActions = json_decode($get('action_sequence') ?? '[]', true);
                        
                        return [
                            'office' => $ownerRecord ? [
                                'id' => $ownerRecord->id,
                                'name' => $ownerRecord->name,
                                'acronym' => $ownerRecord->acronym ?? null,
                            ] : null,
                            'selectedActions' => $selectedActions,
                            'isModal' => false,
                            'fieldName' => 'action_sequence',
                        ];
                    })
                    ->live()
                    ->columnSpanFull(),
            ]);
    }

    private static function previewStep($ownerRecord): Step
    {
        return Step::make('Preview & Confirm')
            ->description('Review your document processing workflow')
            ->icon('heroicon-o-eye')
            ->schema([
                TextEntry::make('workflow_preview')
                    ->label('Document Processing Workflow Preview')
                    ->state(function ($get) use ($ownerRecord) {
                        $classificationId = $get('classification_id');
                        $processName = $get('process_name');
                        
                        // Handle both JSON string and array formats
                        $actionSequenceRaw = $get('action_sequence');
                        $actionSequence = [];
                        
                        if (is_string($actionSequenceRaw)) {
                            $actionSequence = json_decode($actionSequenceRaw, true) ?? [];
                        } elseif (is_array($actionSequenceRaw)) {
                            $actionSequence = $actionSequenceRaw;
                        }
                        
                        if (!$classificationId || !$processName || empty($actionSequence)) {
                            return '⚠️ Please complete the previous steps to see your workflow preview.';
                        }
                        
                        $classification = Classification::find($classificationId);
                        
                        // Sort by step_order for proper sequence display
                        usort($actionSequence, function ($a, $b) {
                            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
                        });
                        
                        $preview = "## 📋 {$processName}\n\n";
                        $preview .= "**Document Classification:** {$classification?->name}\n";
                        $preview .= "**Processing Office:** {$ownerRecord->name}\n";
                        $preview .= "**Total Processing Steps:** " . count($actionSequence) . "\n\n";
                        
                        $preview .= "### 🔄 Document Processing Sequence:\n\n";
                        
                        foreach ($actionSequence as $step) {
                            // Handle both new stepper format and old repeater format
                            $actionId = $step['action_type_id'] ?? $step['id'] ?? null;
                            $actionName = $step['action_name'] ?? $step['name'] ?? null;
                            $stepOrder = $step['step_order'] ?? 'N/A';
                            $resultingStatus = $step['resulting_status'] ?? $step['status_name'] ?? 'Unknown';
                            
                            if ($actionId && !$actionName) {
                                $action = ActionType::find($actionId);
                                $actionName = $action?->name;
                            }
                            
                            if ($actionName) {
                                $preview .= "**Step {$stepOrder}:** {$actionName}\n";
                                $preview .= "- Document status becomes: _{$resultingStatus}_\n";
                                
                                if (!empty($step['step_description'])) {
                                    $preview .= "- Instructions: _{$step['step_description']}_\n";
                                }
                                $preview .= "\n";
                            }
                        }
                        
                        if ($get('workflow_purpose')) {
                            $preview .= "### 📝 Workflow Purpose:\n";
                            $preview .= $get('workflow_purpose') . "\n\n";
                        }
                        
                        $preview .= "### ✅ Ready to Create Document Processing Workflow\n";
                        $preview .= "This workflow aligns with the Document Tracking System flowchart requirements.";
                        
                        return $preview;
                    })
                    ->markdown()
                    ->columnSpanFull(),
            ]);
    }
}

