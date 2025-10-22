<?php

namespace App\Services\Workflow;

use App\Models\ActionType;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\Width;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class ProcessTableBuilder
{
    public function __construct(
        private ProcessFormBuilder $formBuilder,
        private ProcessWorkflowValidator $validator,
        private string $officeId,
        private Model $ownerRecord
    ) {}

    /**
     * Build table columns configuration
     */
    public function buildTableColumns(): array
    {
        return [
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
        ];
    }

    /**
     * Build header actions (Create action)
     */
    public function buildHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::SevenExtraLarge)
                ->modalHeading('Create Document Process Workflow')
                ->mutateDataUsing(function (array $data): array {
                    return $this->mutateCreateData($data);
                })
                ->after(function (Model $record) {
                    $this->handleCreateAfter($record);
                }),
        ];
    }

    /**
     * Build record actions (Edit, View, Delete)
     */
    public function buildRecordActions(): array
    {
        return [
            ActionGroup::make([
                $this->buildEditAction(),
                $this->buildViewAction(),
                $this->buildDeleteAction(),
            ]),
        ];
    }

    /**
     * Build the edit action
     */
    private function buildEditAction(): EditAction
    {
        return EditAction::make()
            ->label('Edit')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalHeading('Edit Process Workflow')
            ->schema($this->formBuilder->buildFormSchema(true))
            ->fillForm(function ($record) {
                return $this->fillEditForm($record);
            })
            ->mutateDataUsing(function (array $data): array {
                return $this->mutateEditData($data);
            })
            ->after(function (Model $record) {
                $this->handleEditAfter($record);
            })
            ->hidden(fn () => !Auth::user()->can('update', $this->ownerRecord));
    }

    /**
     * Build the view action
     */
    private function buildViewAction(): ViewAction
    {
        return ViewAction::make()
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
                                return $this->buildViewWorkflowData($record);
                            })
                            ->visible(function ($record) {
                                return $this->shouldShowWorkflowPreview($record);
                            }),
                    ])
                    ->compact()
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->hidden(fn () => !Auth::user()->can('view', $this->ownerRecord));
    }

    /**
     * Build the delete action
     */
    private function buildDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Delete')
            ->requiresConfirmation()
            ->modalHeading('Delete Process Workflow')
            ->modalDescription('Are you sure you want to delete this process workflow? This action cannot be undone.')
            ->hidden(fn () => !Auth::user()->can('delete', $this->ownerRecord));
    }

    /**
     * Properties to store action types for later attachment
     */
    private array $actionTypesToAttach = [];

    /**
     * Mutate data for create action
     */
    private function mutateCreateData(array $data): array
    {
        // Set process creator and office per ER diagram relationships
        $data['user_id'] = Auth::id(); 
        $data['office_id'] = $this->ownerRecord->id;
        
        // Store action types for workflow sequence
        if (isset($data['action_type_id']) && is_array($data['action_type_id'])) {
            try {
                $this->actionTypesToAttach = $this->validator->validateAndReorderActions($data['action_type_id']);
            } catch (\Exception $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'action_type_id' => $e->getMessage()
                ]);
            }
            unset($data['action_type_id']);
        }
        
        return $data;
    }

    /**
     * Handle after create action
     */
    private function handleCreateAfter(Model $record): void
    {
        if (!empty($this->actionTypesToAttach)) {
            foreach ($this->actionTypesToAttach as $index => $actionTypeId) {
                $record->actions()->attach($actionTypeId, [
                    'sequence_order' => $index + 1,
                    'completed_at' => null,
                    'completed_by' => null,
                    'notes' => null,
                ]);
            }
            
            $this->actionTypesToAttach = [];
        }
    }

    /**
     * Fill edit form data
     */
    private function fillEditForm($record): array
    {
        if (!$record->relationLoaded('actions')) {
            $record->load('actions');
        }
        
        return [
            'name' => $record->name,
            'classification_id' => $record->classification_id,
            'action_type_id' => $record->actions->sortBy('pivot.sequence_order')->pluck('id')->toArray(),
        ];
    }

    /**
     * Mutate data for edit action
     */
    private function mutateEditData(array $data): array
    {
        if (isset($data['action_type_id']) && is_array($data['action_type_id'])) {
            try {
                $this->actionTypesToAttach = $this->validator->validateAndReorderActions($data['action_type_id']);
            } catch (\Exception $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'action_type_id' => $e->getMessage()
                ]);
            }
            unset($data['action_type_id']);
        }
        
        return $data;
    }

    /**
     * Handle after edit action
     */
    private function handleEditAfter(Model $record): void
    {
        if (isset($this->actionTypesToAttach)) {
            $record->actions()->detach();
            
            foreach ($this->actionTypesToAttach as $index => $actionTypeId) {
                $record->actions()->attach($actionTypeId, [
                    'sequence_order' => $index + 1,
                    'completed_at' => null,
                    'completed_by' => null,
                    'notes' => null,
                ]);
            }
            
            $this->actionTypesToAttach = [];
        }
    }

    /**
     * Build workflow data for view action
     */
    private function buildViewWorkflowData($record): array
    {
        if (!$record) {
            return [
                'selectedActions' => [],
                'actionTypes' => collect(),
            ];
        }
        
        if (!$record->relationLoaded('actions')) {
            $record->load('actions');
        }
        
        $selectedActions = $record->actions
            ->sortBy('pivot.sequence_order')
            ->pluck('id')
            ->map([$this, 'toStringId'])
            ->filter(fn($id) => $id !== null)
            ->toArray();
        
        $actionTypes = ActionType::where('office_id', $this->officeId)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
        
        return [
            'selectedActions' => $selectedActions,
            'actionTypes' => $actionTypes,
            'title' => 'Workflow Sequence',
        ];
    }

    /**
     * Determine if workflow preview should be shown
     */
    private function shouldShowWorkflowPreview($record): bool
    {
        if (!$record) return false;
        
        if (!$record->relationLoaded('actions')) {
            $record->load('actions');
        }
        
        return $record->actions->isNotEmpty();
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