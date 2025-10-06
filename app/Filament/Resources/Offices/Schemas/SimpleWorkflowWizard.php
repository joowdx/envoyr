<?php
// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Schemas/SimpleWorkflowWizard.php

namespace App\Filament\Resources\Offices\Schemas;

use App\Models\ActionType;
use App\Models\Classification;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\Repeater;

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
        return Step::make('Arrange Sequence')
            ->description('Put your selected actions in the correct processing order')
            ->icon('heroicon-o-arrows-up-down')
            ->schema([
                Repeater::make('action_sequence')
                    ->label('Document Processing Sequence')
                    ->schema([
                        Select::make('action_type_id')
                            ->label('Action')
                            ->options(fn () => ActionType::where('office_id', $ownerRecord->id)
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $action = ActionType::find($state);
                                    $set('resulting_status', $action?->status_name);
                                    $set('action_name', $action?->name);
                                }
                            })
                            ->helperText('Choose an action from your selected list'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('resulting_status')
                                    ->label('Document Status After Action')
                                    ->disabled()
                                    ->placeholder('Status will appear here'),

                                TextInput::make('step_order')
                                    ->label('Step Number')
                                    ->numeric()
                                    ->default(fn ($get) => count($get('../../action_sequence')))
                                    ->required()
                                    ->helperText('Processing order (1, 2, 3...)'),
                            ]),

                        Textarea::make('step_description')
                            ->label('Processing Instructions')
                            ->placeholder('What should staff do in this step?')
                            ->rows(2)
                            ->helperText('Optional instructions for document processing'),
                    ])
                    ->addActionLabel('Add Processing Step')
                    ->reorderableWithButtons()
                    ->orderColumn('step_order')
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        if (empty($state['action_type_id'])) {
                            return 'New Processing Step';
                        }
                        
                        $action = ActionType::find($state['action_type_id']);
                        $stepOrder = $state['step_order'] ?? 'N/A';
                        
                        return "Step {$stepOrder}: {$action?->name}";
                    })
                    ->minItems(1)
                    ->maxItems(10),
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
                        $actionSequence = $get('action_sequence') ?? [];
                        
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
                            if (!empty($step['action_type_id'])) {
                                $action = ActionType::find($step['action_type_id']);
                                $stepOrder = $step['step_order'] ?? 'N/A';
                                
                                $preview .= "**Step {$stepOrder}:** {$action?->name}\n";
                                $preview .= "- Document status becomes: _{$step['resulting_status']}_\n";
                                
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

