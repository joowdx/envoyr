<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $recordTitleAttribute = 'actionType.name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('action_type_id')
                    ->label('Action Type')
                    ->relationship('actionType', 'name', function ($query) {
                        return $query->where('office_id', $this->ownerRecord->id)
                                   ->where('is_active', true);
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Action Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Review, Approve, Process')
                            ->helperText('What action will this office perform?'),
                        TextInput::make('status_name')
                            ->label('Document Status')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Under Review, Approved, Processing')
                            ->helperText('What status will documents have when this action is applied?'),
                    ])
                    ->createOptionUsing(function ($data) {
                        $data['office_id'] = $this->ownerRecord->id;
                        $data['slug'] = Str::slug($data['name']);
                        $actionType = ActionType::create($data);
                        
                        Notification::make()
                            ->title('Action Type Created')
                            ->body("Action '{$actionType->name}' → Status '{$actionType->status_name}'")
                            ->success()
                            ->send();
                        
                        return $actionType->id;
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('actionType.name')
                    ->label('Action Name')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('actionType.status_name')
                    ->label('Document Status')
                    ->badge()
                    ->color('success')
                    ->description('Status applied to documents'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Actions Defined')
            ->emptyStateDescription('Create actions that this office can perform on documents.')
            ->headerActions([
                CreateAction::make()
                    ->label('Create Action')
                    ->icon('heroicon-s-plus')
                    ->modalHeading('Create Document Action')
                    ->modalDescription('Define what action this office performs and the resulting document status.'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
