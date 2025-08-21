<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CreateOptionAction;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->label('Title'),
                Select::make('classification_id')
                    ->label('Classification')
                    ->relationship('classification', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->createOptionAction(CreateOptionAction::make()
                        ->modalHeading('Create Classification')
                        ->modalDescription('Create a new classification for the document')
                        ->modalSubmitActionLabel('Create')
                        ->modalCancelActionLabel('Cancel')
                        ->modalWidth('md')
                    ),
                TextInput::make('description')
                    ->required()
                    ->label('Description'),
                TextInput::make('file')
            ]);
    }
}
