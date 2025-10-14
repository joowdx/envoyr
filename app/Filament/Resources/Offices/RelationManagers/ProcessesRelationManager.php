<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';

    protected static ?string $recordTitleAttribute = 'status'; 

    protected array $actionTypesToAttach = [];

    public function getTabLabel(): string
    {
        return 'Processes';
    }

    public function form(Schema $schema): Schema // Changed from Schema
    {
        $ownerRecord = $this->ownerRecord;
        
        return $schema
            ->schema([
                TextInput::make('status')
                    ->label('Process Name')
                    ->required(),
                Select::make('classification_id')
                    ->relationship('classification', 'name')
                    ->required()
                    ->label('Classification')
                    ->preload()
                    ->searchable()
                    ->placeholder('Select Classification'),
                
                Select::make('action_type_id')
                    ->label('Action')
                    ->options(fn () => ActionType::where('office_id', $this->ownerRecord->id)
                        ->where('is_active', true)
                        ->pluck('name', 'id'))
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->placeholder('Select Action'),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classification.name')
                    ->label('Classification')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordTitleAttribute('status') 
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('md'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit')
                        ->modalWidth('md')
                        ->hidden(fn () => !Auth::user()->can('update', $this->ownerRecord)),
                    ViewAction::make()
                        ->label('View')
                        ->modalWidth('lg') 
                        ->schema([
                                Section::make('Process Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('status')
                                                    ->label('Process Name')
                                                    ->disabled(), // Read-only
                                                Select::make('classification_id')
                                                    ->relationship('classification', 'name')
                                                    ->label('Classification')
                                                    ->disabled(), // Read-only
                                            ]),
                                    ])
                                    ->compact(),

                                Section::make('Associated Actions')
                                    ->schema([
                                        Repeater::make('actions')
                                            ->label('')
                                            ->relationship('actions') 
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Action Name')
                                                    ->disabled(),
                                                TextInput::make('status_name')
                                                    ->label('Status')
                                                    ->disabled(),
                                                TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->disabled(),
                                            ]),
                            ])
                        ])
                        ->hidden(fn () => !Auth::user()->can('view', $this->ownerRecord)),
                    DeleteAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->hidden(fn () => !Auth::user()->can('delete', $this->ownerRecord)),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id(); 
        $data['processed_at'] = now(); 
        $data['office_id'] = $this->ownerRecord->id;
        $data['status'] = 'in_progress'; // Set initial status
        
        // Store action types separately to attach after creation
        if (isset($data['action_type_id'])) {
            $this->actionTypesToAttach = $data['action_type_id'];
            unset($data['action_type_id']);
        }
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);
        
        // Attach action types with sequence order
        if (!empty($this->actionTypesToAttach)) {
            foreach ($this->actionTypesToAttach as $index => $actionTypeId) {
                $record->actions()->attach($actionTypeId, [
                    'sequence_order' => $index + 1,
                    'completed_at' => null, // Will be set when action is completed
                ]);
            }
        }
        
        return $record;
    }
}
