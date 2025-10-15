<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Actions;
use Filament\Resources\RelationManagers\RelationManager;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';

    protected static ?string $recordTitleAttribute = 'name'; 

    protected array $actionTypesToAttach = [];

    public function getTabLabel(): string
    {
        return 'Processes';
    }

    /**
     * Validate and reorder actions based on prerequisites
     */
    private function validateAndReorderActions(array $actionIds): array
    {
        $actionTypes = ActionType::whereIn('id', $actionIds)
            ->with('prerequisites')
            ->get()
            ->keyBy('id');
        
        // Check if all prerequisites are included
        $missingPrerequisites = $this->findMissingPrerequisites($actionIds, $actionTypes);
        
        if (!empty($missingPrerequisites)) {
            throw new \Exception(
                'Missing prerequisites: ' . implode(', ', $missingPrerequisites) . 
                '. Please include all required prerequisite actions.'
            );
        }
        
        // Auto-order actions based on dependency graph
        return $this->topologicalSort($actionIds, $actionTypes);
    }

    /**
     * Find missing prerequisites for selected actions
     */
    private function findMissingPrerequisites(array $actionIds, $actionTypes): array
    {
        $missing = [];
        
        foreach ($actionIds as $actionId) {
            $action = $actionTypes->get($actionId);
            if (!$action) continue;
            
            foreach ($action->prerequisites as $prerequisite) {
                if (!in_array($prerequisite->id, $actionIds)) {
                    $missing[] = $action->name . ' requires ' . $prerequisite->name;
                }
            }
        }
        
        return array_unique($missing);
    }

    /**
     * Sort actions using topological sort to respect dependencies
     */
    private function topologicalSort(array $actionIds, $actionTypes): array
    {
        $graph = [];
        $inDegree = [];
        
        // Initialize graph and in-degree count
        foreach ($actionIds as $actionId) {
            $graph[$actionId] = [];
            $inDegree[$actionId] = 0;
        }
        
        // Build dependency graph
        foreach ($actionIds as $actionId) {
            $action = $actionTypes->get($actionId);
            if (!$action) continue;
            
            foreach ($action->prerequisites as $prerequisite) {
                if (in_array($prerequisite->id, $actionIds)) {
                    $graph[$prerequisite->id][] = $actionId;
                    $inDegree[$actionId]++;
                }
            }
        }
        
        // Topological sort using Kahn's algorithm
        $queue = [];
        $result = [];
        
        // Find all nodes with no incoming edges
        foreach ($inDegree as $node => $degree) {
            if ($degree == 0) {
                $queue[] = $node;
            }
        }
        
        while (!empty($queue)) {
            $current = array_shift($queue);
            $result[] = $current;
            
            foreach ($graph[$current] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] == 0) {
                    $queue[] = $neighbor;
                }
            }
        }
        
        // Check for circular dependencies
        if (count($result) != count($actionIds)) {
            throw new \Exception('Circular dependency detected in action prerequisites.');
        }
        
        return $result;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Process Details')
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
                    ->columnSpan(2),
                
                Section::make('Workflow Configuration')
                    ->description('Configure the actions that will be performed in this process')
                    ->schema([
                        Select::make('action_type_id')
                            ->label('Workflow Actions')
                            ->multiple()
                            ->placeholder('Select actions for this process workflow')
                            ->options(function () {
                                return ActionType::where('office_id', $this->ownerRecord->id)
                                    ->where('is_active', true)
                                    ->with('prerequisites')
                                    ->get()
                                    ->mapWithKeys(function ($action) {
                                        $prereqText = '';
                                        if ($action->prerequisites->isNotEmpty()) {
                                            $prereqNames = $action->prerequisites->pluck('name')->join(', ');
                                            $prereqText = " (requires: {$prereqNames})";
                                        }
                                        return [$action->id => $action->name . $prereqText];
                                    })
                                    ->toArray();
                            })
                            ->helperText('Actions will be automatically ordered based on prerequisites. Make sure to include all required prerequisite actions.')
                            ->searchable()
                            ->optionsLimit(50)
                            ->noSearchResultsMessage('No actions found. Please create action types first.')
                            ->minItems(1)
                            ->maxItems(10)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Validate selections in real-time
                                if (!empty($state)) {
                                    try {
                                        $orderedActions = $this->validateAndReorderActions($state);
                                        // Update the stepper to show the corrected order
                                        $set('stepper_data', $orderedActions);
                                        $set('original_order', $state !== $orderedActions);
                                    } catch (\Exception $e) {
                                        // Clear stepper on validation error
                                        $set('stepper_data', []);
                                        $set('validation_error', $e->getMessage());
                                    }
                                } else {
                                    $set('stepper_data', []);
                                    $set('validation_error', null);
                                }
                            }),
                    ])
                    ->columnSpan(2),

                Section::make('Workflow Preview')
                    ->description('Visual representation of the configured workflow sequence')
                    ->schema([
                        ViewField::make('workflow_preview')
                            ->view('components.workflow-stepper')
                            ->viewData(function ($get) {
                                $orderedActions = $get('stepper_data') ?? [];
                                $validationError = $get('validation_error');
                                $wasReordered = $get('original_order') ?? false;
                                
                                if ($validationError) {
                                    return [
                                        'selectedActions' => [],
                                        'actionTypes' => collect(),
                                        'validationError' => $validationError,
                                    ];
                                }
                                
                                $actionTypes = ActionType::where('office_id', $this->ownerRecord->id)
                                    ->where('is_active', true)
                                    ->get()
                                    ->keyBy('id');
                                
                                return [
                                    'selectedActions' => $orderedActions,
                                    'actionTypes' => $actionTypes,
                                    'wasReordered' => $wasReordered,
                                ];
                            })
                            ->visible(fn ($get) => !empty($get('action_type_id'))),
                    ])
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['actions', 'classification']))
            ->columns([
                TextColumn::make('name')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        return "For " . ($record->classification->name ?? 'Unknown') . " documents";
                    }),
                TextColumn::make('classification.name')
                    ->label('Document Classification')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('actions_count')
                    ->label('Actions Count')
                    ->counts('actions')
                    ->badge()
                    ->sortable()
                    ->description('Number of workflow actions'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordTitleAttribute('name') 
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::SevenExtraLarge) // Responsive modal for workflow steps
                    ->modalHeading('Create Document Process Workflow')
                    ->mutateDataUsing(function (array $data): array {
                        // Set process creator and office per ER diagram relationships
                        $data['user_id'] = Auth::id(); 
                        $data['office_id'] = $this->ownerRecord->id;
                        
                        // Store action types for workflow sequence (follows document flowchart)
                        if (isset($data['action_type_id']) && is_array($data['action_type_id'])) {
                            // Validate and auto-order actions based on prerequisites
                            try {
                                $this->actionTypesToAttach = $this->validateAndReorderActions($data['action_type_id']);
                            } catch (\Exception $e) {
                                // Use Laravel's validation exception
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'action_type_id' => $e->getMessage()
                                ]);
                            }
                            unset($data['action_type_id']);
                        }
                        
                        return $data;
                    })
                    ->after(function (Model $record) {
                        // Create workflow sequence per document processing flowchart
                        if (!empty($this->actionTypesToAttach)) {
                            foreach ($this->actionTypesToAttach as $index => $actionTypeId) {
                                $record->actions()->attach($actionTypeId, [
                                    'sequence_order' => $index + 1,
                                    'completed_at' => null,
                                    'completed_by' => null,
                                    'notes' => null,
                                ]);
                            }
                            
                            // Clear the stored actions
                            $this->actionTypesToAttach = [];
                        }
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit')
                        ->modalWidth(Width::SevenExtraLarge)
                        ->modalHeading('Edit Process Workflow')
                        ->schema([
                            Section::make('Process Details')
                                ->description('Update the process workflow for document handling')
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
                                ->columnSpan(2),
                            
                            Section::make('Workflow Configuration')
                                ->description('Update the actions that will be performed in this process')
                                ->schema([
                                    Select::make('action_type_id')
                                        ->label('Workflow Actions')
                                        ->multiple()
                                        ->placeholder('Select actions for this process workflow')
                                        ->options(function () {
                                            return ActionType::where('office_id', $this->ownerRecord->id)
                                                ->where('is_active', true)
                                                ->with('prerequisites')
                                                ->get()
                                                ->mapWithKeys(function ($action) {
                                                    $prereqText = '';
                                                    if ($action->prerequisites->isNotEmpty()) {
                                                        $prereqNames = $action->prerequisites->pluck('name')->join(', ');
                                                        $prereqText = " (requires: {$prereqNames})";
                                                    }
                                                    return [$action->id => $action->name . $prereqText];
                                                })
                                                ->toArray();
                                        })
                                        ->helperText('Actions will be automatically ordered based on prerequisites. Make sure to include all required prerequisite actions.')
                                        ->searchable()
                                        ->optionsLimit(50)
                                        ->noSearchResultsMessage('No actions found. Please create action types first.')
                                        ->minItems(1)
                                        ->maxItems(10)
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            // Validate selections in real-time
                                            if (!empty($state)) {
                                                try {
                                                    $orderedActions = $this->validateAndReorderActions($state);
                                                    // Update the stepper to show the corrected order
                                                    $set('stepper_data', $orderedActions);
                                                    $set('original_order', $state !== $orderedActions);
                                                } catch (\Exception $e) {
                                                    // Clear stepper on validation error
                                                    $set('stepper_data', []);
                                                    $set('validation_error', $e->getMessage());
                                                }
                                            } else {
                                                $set('stepper_data', []);
                                                $set('validation_error', null);
                                            }
                                        }),
                                ])
                                ->columnSpan(2),

                            Section::make('Workflow Preview')
                                ->description('Visual representation of the configured workflow sequence')
                                ->schema([
                                    ViewField::make('workflow_preview')
                                        ->view('components.workflow-stepper')
                                        ->viewData(function ($get) {
                                            $orderedActions = $get('stepper_data') ?? [];
                                            $validationError = $get('validation_error');
                                            $wasReordered = $get('original_order') ?? false;
                                            
                                            if ($validationError) {
                                                return [
                                                    'selectedActions' => [],
                                                    'actionTypes' => collect(),
                                                    'validationError' => $validationError,
                                                ];
                                            }
                                            
                                            $actionTypes = ActionType::where('office_id', $this->ownerRecord->id)
                                                ->where('is_active', true)
                                                ->get()
                                                ->keyBy('id');
                                            
                                            return [
                                                'selectedActions' => $orderedActions,
                                                'actionTypes' => $actionTypes,
                                                'wasReordered' => $wasReordered,
                                            ];
                                        })
                                        ->visible(fn ($get) => !empty($get('action_type_id'))),
                                ])
                                ->columnSpan(2),
                        ])
                        ->fillForm(function ($record) {
                            // Load the current actions for the process
                            if (!$record->relationLoaded('actions')) {
                                $record->load('actions');
                            }
                            
                            return [
                                'name' => $record->name,
                                'classification_id' => $record->classification_id,
                                'action_type_id' => $record->actions->sortBy('pivot.sequence_order')->pluck('id')->toArray(),
                            ];
                        })
                        ->mutateDataUsing(function (array $data): array {
                            // Store action types for later sync
                            if (isset($data['action_type_id']) && is_array($data['action_type_id'])) {
                                // Validate and auto-order actions based on prerequisites
                                try {
                                    $this->actionTypesToAttach = $this->validateAndReorderActions($data['action_type_id']);
                                } catch (\Exception $e) {
                                    // Use Laravel's validation exception
                                    throw \Illuminate\Validation\ValidationException::withMessages([
                                        'action_type_id' => $e->getMessage()
                                    ]);
                                }
                                unset($data['action_type_id']);
                            }
                            
                            return $data;
                        })
                        ->after(function (Model $record) {
                            // Sync workflow actions (this will add/remove actions as needed)
                            if (isset($this->actionTypesToAttach)) {
                                // Detach all existing actions first
                                $record->actions()->detach();
                                
                                // Attach new actions with sequence order
                                foreach ($this->actionTypesToAttach as $index => $actionTypeId) {
                                    $record->actions()->attach($actionTypeId, [
                                        'sequence_order' => $index + 1,
                                        'completed_at' => null,
                                        'completed_by' => null,
                                        'notes' => null,
                                    ]);
                                }
                                
                                // Clear the stored actions
                                $this->actionTypesToAttach = [];
                            }
                        })
                        ->hidden(fn () => !Auth::user()->can('update', $this->ownerRecord)),
                    ViewAction::make()
                        ->label('View')
                        ->modalWidth(Width::FiveExtraLarge)
                        ->modalHeading('Process Workflow Details')
                        ->schema([
                            Section::make('Process Information')
                                ->description('Process details for document workflow tracking')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Process Name')
                                                ->disabled(),
                                            Select::make('classification_id')
                                                ->relationship('classification', 'name')
                                                ->label('Document Classification')
                                                ->disabled(),
                                        ]),
                                ])
                                ->compact(),

                            Section::make('Workflow Visualization')
                                ->description('Visual representation of the process workflow')
                                ->schema([
                                    ViewField::make('workflow_preview')
                                        ->view('components.workflow-stepper')
                                        ->viewData(function ($record) {
                                            if (!$record) {
                                                return [
                                                    'selectedActions' => [],
                                                    'actionTypes' => collect(),
                                                ];
                                            }
                                            
                                            // Force load the actions if not already loaded
                                            if (!$record->relationLoaded('actions')) {
                                                $record->load('actions');
                                            }
                                            
                                            $selectedActions = $record->actions
                                                ->sortBy('pivot.sequence_order')
                                                ->pluck('id')
                                                ->toArray();
                                            
                                            $actionTypes = ActionType::where('office_id', $this->ownerRecord->id)
                                                ->where('is_active', true)
                                                ->get()
                                                ->keyBy('id');
                                            
                                            return [
                                                'selectedActions' => $selectedActions,
                                                'actionTypes' => $actionTypes,
                                                'title' => 'Workflow Sequence',
                                            ];
                                        })
                                        ->visible(function ($record) {
                                            if (!$record) return false;
                                            
                                            if (!$record->relationLoaded('actions')) {
                                                $record->load('actions');
                                            }
                                            
                                            return $record->actions->isNotEmpty();
                                        }),
                                ])
                                ->compact()
                                ->collapsible()
                                ->collapsed(false),
                        ])
                        ->hidden(fn () => !Auth::user()->can('view', $this->ownerRecord)),
                    DeleteAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Process Workflow')
                        ->modalDescription('Are you sure you want to delete this process workflow? This action cannot be undone.')
                        ->hidden(fn () => !Auth::user()->can('delete', $this->ownerRecord)),
                ]),
            ]);
    }
}
