<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';

    protected static ?string $recordTitleAttribute = 'classification.name'; // ✅ Fixed inconsistency

    public function getTabLabel(): string
    {
        return 'Processes';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('process_name')
                    ->label('Process Name')
                    ->required(),
                Select::make('classification_id')
                    ->relationship('classification', 'name')
                    ->required()
                    ->label('Classification')
                    ->preload()
                    ->searchable()
                    ->placeholder('Select Classification')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('process_name')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classification.name')
                    ->label('Classification')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordTitleAttribute('classification.name') // ✅ Fixed inconsistency
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
