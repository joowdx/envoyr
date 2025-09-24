<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Tabs\Tab;

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
                Select::make('prerequisite_action_type_id')
                    ->label('Prerequisite Action')
                    ->options(ActionType::where('office_id', $this->ownerRecord->id)->pluck('name', 'id'))
                    ->searchable()
                    ->helperText('Select an action that must be performed before this one'),
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
                TextColumn::make('prerequisiteActionType.name')
                    ->label('Prerequisite')
                    ->badge()
                    ->color('gray')
                    ->placeholder('None'),
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
                    ->modalHeading('Create New Action')
                    ->modalWidth('lg')
                    ->modalDescription('Define a new action for this office.'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['office_id'] = $this->ownerRecord->id;
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = true;
        
        return $data;
    }
}
