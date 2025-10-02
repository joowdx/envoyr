<?php
// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Schemas/ProcessWorkflowSchema.php

namespace App\Filament\Resources\Offices\Schemas;

use App\Models\ActionType;
use App\Models\Classification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
        return Step::make('Define Action Sequence')
            ->description('Create the sequential workflow of actions')
            ->schema([
                self::actionWorkflowRepeater($ownerRecord),
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
            ->reorderable('step_order')
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
        $actionWorkflow = $get('action_workflow') ?? [];
        
        if (!$classificationId || !$processName || empty($actionWorkflow)) {
            return 'Please complete the previous steps to see the workflow summary.';
        }
        
        $classification = Classification::find($classificationId);
        
        $summary = "**Process:** {$processName}\n";
        $summary .= "**Classification:** {$classification?->name}\n";
        $summary .= "**Office:** {$ownerRecord->name}\n\n";
        $summary .= "**Sequential Workflow Steps:**\n";
        
        // Sort by step_order
        usort($actionWorkflow, function ($a, $b) {
            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
        });
        
        foreach ($actionWorkflow as $step) {
            if (!empty($step['action_type_id'])) {
                $action = ActionType::find($step['action_type_id']);
                $stepOrder = $step['step_order'] ?? 'N/A';
                $actionName = $action?->name ?? 'Unknown Action';
                $status = $step['resulting_status'] ?? 'Unknown Status';
                
                $summary .= "{$stepOrder}. **{$actionName}** → Status: _{$status}_\n";
                
                if (!empty($step['step_description'])) {
                    $summary .= "   _{$step['step_description']}_\n";
                }
                $summary .= "\n";
            }
        }
        
        return $summary;
    }
}