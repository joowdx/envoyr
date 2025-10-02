<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\Classification;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Offices\Schemas\ProcessInfolist;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';
    protected static ?string $recordTitleAttribute = 'name';

    public function getTabLabel(): string
    {
        return 'Document Process Workflows';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->classification->name ?? 'No classification'),
                    
                TextColumn::make('classification.name')
                    ->label('Classification')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                    
                TextColumn::make('action_sequence_count')
                    ->label('Action Steps')
                    ->getStateUsing(function ($record) {
                        // Count actions in the process sequence
                        $actionSequence = json_decode($record->action_sequence ?? '[]', true);
                        return count($actionSequence) . ' step(s)';
                    })
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Process Workflows')
            ->emptyStateDescription('Create document processing workflows for specific classifications.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->recordTitleAttribute('name')
            ->headerActions([

                Action::make('process_workflow_wizard')
                    ->label('Define Process Workflow')
                    ->icon('heroicon-s-cog-6-tooth')
                    ->color('success')
                    ->modalHeading('Document Process Workflow Designer')
                    ->modalDescription('Create a sequential workflow process for document classifications.')
                    ->modalWidth('4xl')
                    ->schema([
                        Wizard::make([
                            Step::make('Select Classification')
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
                                ]),
                                
                            Step::make('Define Action Sequence')
                                ->description('Create the sequential workflow of actions')
                                ->schema([
                                    Repeater::make('action_workflow')
                                        ->label('Sequential Action Workflow')
                                        ->schema([
                                            Select::make('action_type_id')
                                                ->label('Action')
                                                ->options(function () {
                                                    return ActionType::where('office_id', $this->ownerRecord->id)
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
                                        ->helperText('Define the sequential steps for processing documents of this classification'),
                                ]),
                                
                            Step::make('Workflow Validation')
                                ->description('Review and validate the workflow sequence')
                                ->schema([
                                    TextEntry::make('workflow_summary')
                                        ->label('Workflow Summary')
                                        ->state(function ($get) {
                                            $classificationId = $get('classification_id');
                                            $processName = $get('process_name');
                                            $actionWorkflow = $get('action_workflow') ?? [];
                                            
                                            if (!$classificationId || !$processName || empty($actionWorkflow)) {
                                                return 'Please complete the previous steps to see the workflow summary.';
                                            }
                                            
                                            $classification = Classification::find($classificationId);
                                            
                                            $summary = "**Process:** {$processName}\n";
                                            $summary .= "**Classification:** {$classification?->name}\n";
                                            $summary .= "**Office:** {$this->ownerRecord->name}\n\n";
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
                                        })
                                        ->markdown()
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->skippable()
                        ->persistStepInQueryString()
                    ])
                    ->action(function (array $data) {
                        $this->createProcessWorkflow($data);
                    })
                    ->modalSubmitActionLabel('Create Process Workflow')
                    ->modalSubmitAction(fn (Action $action) => 
                        $action->color('success')->icon('heroicon-s-cog-6-tooth')
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading(fn ($record) => "Process: {$record->name}")
                        ->modalDescription('View document process workflow details')
                        ->modalWidth('3xl')
                        ->schema(fn (): array => ProcessInfolist::schema()),
                        
                    Action::make('edit_workflow')
                        ->label('Edit Workflow')
                        ->icon('heroicon-s-pencil')
                        ->color('warning')
                        ->modalHeading(fn ($record) => "Edit Process: {$record->name}")
                        ->modalDescription('Modify the sequential workflow for this process')
                        ->modalWidth('3xl')
                        ->fillForm(function ($record) {
                            $actionSequence = json_decode($record->action_sequence ?? '[]', true);
                            return [
                                'process_name' => $record->name,
                                'classification_id' => $record->classification_id,
                                'process_description' => $record->description,
                                'action_workflow' => $actionSequence,
                            ];
                        })
                        ->schema([
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
                                
                            Repeater::make('action_workflow')
                                ->label('Action Workflow')
                                ->schema([
                                    Select::make('action_type_id')
                                        ->label('Action')
                                        ->options(function () {
                                            return ActionType::where('office_id', $this->ownerRecord->id)
                                                ->where('is_active', true)
                                                ->pluck('name', 'id');
                                        })
                                        ->required(),
                                        
                                    TextInput::make('step_order')
                                        ->label('Step Order')
                                        ->numeric()
                                        ->required(),
                                        
                                    Textarea::make('step_description')
                                        ->label('Description')
                                        ->rows(2),
                                ])
                                ->reorderable('step_order')
                                ->orderColumn('step_order'),
                        ])
                        ->action(function ($record, array $data) {
                            $this->updateProcessWorkflow($record, $data);
                        }),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Process Workflow')
                        ->modalDescription('Are you sure you want to delete this workflow? This action cannot be undone.')
                        ->hidden(fn () => !Auth::user()->can('delete', $this->ownerRecord)),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id(); 
        $data['office_id'] = $this->ownerRecord->id;
        
        return $data;
    }

    /**
     * Create process workflow according to Document Tracking System
     */
    protected function createProcessWorkflow(array $data): void
    {
        $actionWorkflow = $data['action_workflow'] ?? [];
        
        if (empty($actionWorkflow)) {
            Notification::make()
                ->title('No Workflow Steps Defined')
                ->body('Please define at least one workflow step.')
                ->danger()
                ->send();
            return;
        }

        // Sort workflow by step_order
        usort($actionWorkflow, function ($a, $b) {
            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
        });

        // Create the process workflow record
        $process = \App\Models\Process::create([
            'name' => $data['process_name'],
            'classification_id' => $data['classification_id'],
            'description' => $data['process_description'] ?? null,
            'office_id' => $this->ownerRecord->id,
            'user_id' => Auth::id(),
            'action_sequence' => json_encode($actionWorkflow),
            'status' => 'Draft', // Initial status for process templates
            'processed_at' => now(),
        ]);

        Notification::make()
            ->title('Process Workflow Created')
            ->body("Created workflow '{$data['process_name']}' with " . count($actionWorkflow) . " sequential steps.")
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    /**
     * Update process workflow
     */
    protected function updateProcessWorkflow($record, array $data): void
    {
        $actionWorkflow = $data['action_workflow'] ?? [];
        
        // Sort workflow by step_order
        usort($actionWorkflow, function ($a, $b) {
            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
        });

        $record->update([
            'name' => $data['process_name'],
            'classification_id' => $data['classification_id'],
            'description' => $data['process_description'] ?? null,
            'action_sequence' => json_encode($actionWorkflow),
        ]);

        Notification::make()
            ->title('Process Workflow Updated')
            ->body("Updated workflow '{$data['process_name']}' with " . count($actionWorkflow) . " sequential steps.")
            ->success()
            ->send();

        $this->dispatch('refresh');
    }
}
