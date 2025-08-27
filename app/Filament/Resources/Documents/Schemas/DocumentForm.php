<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

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
                    ->createOptionForm(function (Schema $schema) {
                        return $schema->components([
                            TextInput::make('name')
                                ->required()
                                ->label('Name'),
                            TextInput::make('description')
                                ->required()
                                ->label('Description')
                                ->columnSpanFull(),
                        ]);
                    })
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('source_id')
                    ->label('Source')
                    ->relationship('source', 'name')
                    ->createOptionForm(function (Schema $schema) {
                        return $schema->components([
                            TextInput::make('name')
                                ->required()
                                ->label('Name'),
                            TextInput::make('description')
                                ->required()
                                ->label('Description')
                                ->columnSpanFull(),
                        ]);
                    })
                    ->preload()
                    ->searchable(),
                TextInput::make('description')
                    ->required()
                    ->label('Description'),
                TextInput::make('file')
            ]);
    }
}
