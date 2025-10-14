<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\RelationManagers\RelationManager;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actionTypes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTabComponent(Model $ownerRecord, string $pageClass): Tab
    {
        return Tab::make('Actions');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('prerequisites')
                    ->label('Prerequisite Actions')
                    ->options(ActionType::where('office_id', $this->ownerRecord->id)->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->helperText('Select actions that must be performed before this one (multiple allowed)'),
                TextInput::make('name')
                    ->label('Action Name')
                    ->required()
                    ->placeholder('e.g., Review, Approve, Process')
                    ->helperText('What action will this office perform?'),
                TextInput::make('status_name')
                    ->label('Document Status')
                    ->required()
                    ->placeholder('e.g., Under Review, Approved, Processing')
                    ->helperText('What status will documents have when this action is applied?'),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Actions')
            ->columns([
                TextColumn::make('name')
                    ->label('Action Name')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('status_name')
                    ->label('Document Status')
                    ->badge()
                    ->color('success')
                    ->description('Status applied to documents'),
                TextColumn::make('prerequisites_count')
                    ->label('Prerequisites')
                    ->badge()
                    ->color('gray')
                    ->getStateUsing(fn (ActionType $record) => $record->prerequisites->count())
                    ->description(fn (ActionType $record) => $record->prerequisites->pluck('name')->join(', ') ?: 'None'),
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
                    ->createAnother(false)
                    ->icon('heroicon-s-plus')
                    ->modalHeading('Create New Action')
                    ->modalWidth('lg')
                    ->modalDescription('Define a new action for this office.')
                    ->before(function (CreateAction $action, array $data) {
                        $exists = ActionType::where('office_id', $this->ownerRecord->id)
                            ->where('name', $data['name'])
                            ->exists();
                            
                        if ($exists) {
                            Notification::make()
                                ->title('Action Already Exists')
                                ->body("An action named '{$data['name']}' already exists for this office. Please choose a different name.")
                                ->warning()
                                ->persistent()
                                ->send();
                                
                            $action->halt(); 
                        }
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth('md'),
                    ViewAction::make()
                        ->modalWidth('md')
                        ->schema([
                            Select::make('prerequisites')
                                ->label('Prerequisite Actions')
                                ->options(ActionType::where('office_id', $this->ownerRecord->id)->pluck('name', 'id'))
                                ->multiple()
                                ->disabled(), 
                            TextInput::make('name')
                                ->label('Action Name')
                                ->disabled(), 
                            TextInput::make('status_name')
                                ->label('Document Status')
                                ->disabled(),
                        ]),
                    DeleteAction::make(),
                    RestoreAction::make(),
                ])
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['office_id'] = $this->ownerRecord->id;
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle prerequisites attachment after save
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->prerequisites()->attach($this->form->getState()['prerequisites'] ?? []);
    }

    protected function afterSave(): void
    {
        $this->record->prerequisites()->sync($this->form->getState()['prerequisites'] ?? []);
    }
}
