<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Offices\OfficeResource;
use Filament\Resources\RelationManagers\RelationManager;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';

    protected static ?string $relatedResource = OfficeResource::class;

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                TextColumn::make('process_name')
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
            ->recordTitleAttribute('classification_id')
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
