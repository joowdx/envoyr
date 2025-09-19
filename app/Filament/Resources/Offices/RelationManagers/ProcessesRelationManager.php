<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';

    protected static ?string $recordTitleAttribute = 'status'; 

    protected array $actionTypesToAttach = [];

    public function getTabLabel(): string
    {
        return 'Processes';
    }

    public function form(Schema $schema): Schema
    {
        $ownerRecord = $this->ownerRecord;
        
        return $schema
            ->columns(1)
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
                Repeater::make('action_types') 
                    ->label('Actions')
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
                            ->placeholder('Select Action'),
                    ])
                    ->addActionLabel('Add Action')
                    ->columns(1),
                ]);
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
                        ->modalWidth('md')
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
        
        // Store action types separately to attach after creation
        if (isset($data['action_types'])) {
            $this->actionTypesToAttach = collect($data['action_types'])->pluck('action_type_id')->toArray();
            unset($data['action_types']); // Remove from main data as it's not a Process field
        }
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);
        
        // Attach action types if they exist
        if (isset($this->actionTypesToAttach) && !empty($this->actionTypesToAttach)) {
            $record->actions()->attach($this->actionTypesToAttach);
        }
        
        return $record;
    }
}
