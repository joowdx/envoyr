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
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->helperText('Select the actions that will be performed in this process workflow. Actions will be executed in the order selected.')
                            ->searchable()
                            ->optionsLimit(50)
                            ->noSearchResultsMessage('No actions found. Please create action types first.')
                            ->minItems(1)
                            ->maxItems(10)
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Trigger stepper update
                                $set('stepper_data', $state);
                            }),
                        
                        ViewField::make('workflow_preview')
                            ->view('components.workflow-stepper')
                            ->viewData(function ($get) {
                                $selectedActions = $get('action_type_id') ?? [];
                                $actionTypes = ActionType::where('office_id', $this->ownerRecord->id)
                                    ->where('is_active', true)
                                    ->get()
                                    ->keyBy('id');
                                
                                return [
                                    'selectedActions' => $selectedActions,
                                    'actionTypes' => $actionTypes,
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
                            $this->actionTypesToAttach = $data['action_type_id'];
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

                            Section::make('Workflow Actions')
                                ->description('Detailed list of actions in this process')
                                ->schema([
                                    Textarea::make('actions_list')
                                        ->label('Process Workflow')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->formatStateUsing(function ($record) {
                                            if (!$record) {
                                                return 'No record found';
                                            }
                                            
                                            // Force load the actions if not already loaded
                                            if (!$record->relationLoaded('actions')) {
                                                $record->load('actions');
                                            }
                                            
                                            if ($record->actions->isEmpty()) {
                                                return "📋 No workflow actions assigned to this process.\n\nTo add actions:\n1. Click Edit to modify this process\n2. Select the workflow actions needed\n3. Save to update the process";
                                            }
                                            
                                            $actionsList = $record->actions
                                                ->sortBy('pivot.sequence_order')
                                                ->map(function ($action, $index) {
                                                    $order = $action->pivot->sequence_order ?? ($index + 1);
                                                    $status = $action->status_name ?? 'Pending';
                                                    $statusIcon = match($status) {
                                                        'Completed' => '✅',
                                                        'In Progress' => '🔄',
                                                        'Pending' => '⏳',
                                                        default => '📋'
                                                    };
                                                    return "{$statusIcon} Step {$order}: {$action->name}\n   Status: {$status}";
                                                })
                                                ->join("\n\n");
                                                
                                            $totalActions = $record->actions->count();
                                            $header = "🔄 WORKFLOW SEQUENCE ({$totalActions} actions)\n" . str_repeat("─", 40) . "\n\n";
                                            
                                            return $header . $actionsList;
                                        })
                                        ->rows(10)
                                        ->extraAttributes(['style' => 'font-family: monospace; line-height: 1.4;']),
                                ])
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
