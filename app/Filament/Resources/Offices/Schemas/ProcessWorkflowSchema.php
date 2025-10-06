<?php
// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Schemas/ProcessWorkflowSchema.php

namespace App\Filament\Resources\Offices\Schemas;

use App\Models\ActionType;
use App\Models\Classification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Infolists\Components\TextEntry;

class ProcessWorkflowSchema
{
    public static function wizardSchema($ownerRecord): array
    {
        return [
            Wizard::make([
                self::classificationStep(),
                self::actionSequenceStep($ownerRecord),
                self::validationStep($ownerRecord),
            ])
            ->skippable()
            ->persistStepInQueryString()
        ];
    }

    public static function editSchema($ownerRecord): array
    {
        return [
            TextInput::make('process_name')
                ->label('Process Name')
                ->required(),
                
            Select::make('classification_id')
                ->options(Classification::pluck('name', 'id'))
                ->required()
                ->label('Classification'),
                
            Textarea::make('process_description')
                ->label('Description')
                ->rows(3),
                
            self::actionWorkflowRepeater($ownerRecord),
        ];
    }

    private static function classificationStep(): Step
    {
        return Step::make('Select Classification')
            ->description('Choose the document classification for this workflow')
            ->schema([
                Select::make('classification_id')
                    ->label('Document Classification')
                    ->options(Classification::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->placeholder('Select Classification (e.g., Payroll, Legal, Budget)')
                    ->helperText('Classification type this workflow will process'),
                    
                TextInput::make('process_name')
                    ->label('Process Workflow Name')
                    ->required()
                    ->placeholder('e.g., Payroll Document Processing Workflow')
                    ->helperText('Descriptive name for this workflow process'),
                    
                Textarea::make('process_description')
                    ->label('Workflow Description')
                    ->placeholder('Describe the purpose and scope of this document processing workflow...')
                    ->rows(3)
                    ->helperText('Optional description explaining the workflow'),
            ]);
    }

    private static function actionSequenceStep($ownerRecord): Step
    {
        return Step::make('Build Action Sequence')
            ->description('Select actions to add them to your workflow sequence')
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
                                // Add to sequence
                                $currentSequence[] = [
                                    'id' => $action->id,
                                    'name' => $action->name,
                                    'status_name' => $action->status_name,
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
                            'office' => $ownerRecord,
                            'selectedActions' => $selectedActions,
                            'isModal' => false,
                            'fieldName' => 'action_sequence',
                        ];
                    })
                    ->columnSpanFull(),
            ]);
    }

    private static function validationStep($ownerRecord): Step
    {
        return Step::make('Workflow Validation')
            ->description('Review and validate the workflow sequence')
            ->schema([
                TextEntry::make('workflow_summary')
                    ->label('Workflow Summary')
                    ->state(function ($get) use ($ownerRecord) {
                        return self::generateWorkflowSummary($get, $ownerRecord);
                    })
                    ->markdown()
                    ->columnSpanFull(),
            ]);
    }

    private static function actionWorkflowRepeater($ownerRecord): Repeater
    {
        return Repeater::make('action_workflow')
            ->label('Sequential Action Workflow')
            ->schema([
                Select::make('action_type_id')
                    ->label('Action')
                    ->options(function () use ($ownerRecord) {
                        return ActionType::where('office_id', $ownerRecord->id)
                            ->where('is_active', true)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $action = ActionType::find($state);
                            $set('resulting_status', $action?->status_name);
                        }
                    })
                    ->helperText('Select action to perform at this step'),
                    
                TextInput::make('resulting_status')
                    ->label('Resulting Status')
                    ->disabled()
                    ->helperText('Document status after this action'),
                    
                TextInput::make('step_order')
                    ->label('Step Order')
                    ->numeric()
                    ->default(fn ($get) => count($get('../../action_workflow')) + 1)
                    ->required()
                    ->helperText('Order of execution (1, 2, 3, etc.)'),
                    
                Textarea::make('step_description')
                    ->label('Step Description')
                    ->placeholder('Describe what happens in this step...')
                    ->rows(2)
                    ->helperText('Optional description of this workflow step'),
            ])
            ->collapsible()
            ->reorderable()
            ->orderColumn('step_order')
            ->minItems(1)
            ->maxItems(20)
            ->addActionLabel('Add Workflow Step')
            ->reorderableWithButtons()
            ->helperText('Define the sequential steps for processing documents of this classification');
    }

    private static function generateWorkflowSummary($get, $ownerRecord): string
    {
        $classificationId = $get('classification_id');
        $processName = $get('process_name');
        
        // Check for stepper data first, then fall back to repeater data
        $actionSequence = [];
        
        if (!empty($get('action_sequence'))) {
            // Stepper data - convert from JSON
            $stepperActions = json_decode($get('action_sequence'), true);
            if (!empty($stepperActions)) {
                $actionSequence = array_map(function ($action, $index) {
                    return [
                        'action_type_id' => $action['id'],
                        'step_order' => $index + 1,
                        'resulting_status' => $action['status_name'] ?? 'Unknown',
                        'step_description' => "Perform {$action['name']}",
                        'action_name' => $action['name']
                    ];
                }, $stepperActions, array_keys($stepperActions));
            }
        } elseif (!empty($get('action_workflow'))) {
            // Traditional repeater data
            $actionSequence = $get('action_workflow');
        }
        
        if (!$classificationId || !$processName || empty($actionSequence)) {
            return '**Next Steps:**
            
1. Select a document classification
2. Give your workflow a name  
3. Use the visual stepper to add actions by clicking them
4. Review your workflow here
            
*Your workflow summary will appear here once you complete the steps above.*';
        }
        
        $classification = Classification::find($classificationId);
        
        $summary = "## 📋 Workflow Summary\n\n";
        $summary .= "**Process Name:** {$processName}\n";
        $summary .= "**Classification:** {$classification?->name}\n";
        $summary .= "**Office:** {$ownerRecord->name}\n";
        $summary .= "**Total Steps:** " . count($actionSequence) . "\n\n";
        $summary .= "### 🔄 Action Sequence\n\n";
        
        // Sort by step_order
        usort($actionSequence, function ($a, $b) {
            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
        });
        
        foreach ($actionSequence as $step) {
            if (!empty($step['action_type_id']) || !empty($step['action_name'])) {
                $stepOrder = $step['step_order'] ?? 'N/A';
                
                // Handle both stepper and repeater data formats
                if (!empty($step['action_name'])) {
                    $actionName = $step['action_name'];
                } else {
                    $action = ActionType::find($step['action_type_id']);
                    $actionName = $action?->name ?? 'Unknown Action';
                }
                
                $status = $step['resulting_status'] ?? 'Unknown Status';
                
                $summary .= "**Step {$stepOrder}:** {$actionName}\n";
                $summary .= "└─ *Status after action: {$status}*\n";
                
                if (!empty($step['step_description'])) {
                    $summary .= "└─ _{$step['step_description']}_\n";
                }
                $summary .= "\n";
            }
        }
        
        $summary .= "\n✅ **Ready to create this workflow!**";
        
        return $summary;
    }
}