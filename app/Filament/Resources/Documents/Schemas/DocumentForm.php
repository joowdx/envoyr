<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Toggle::make('electronic')
                    ->required(),
                Toggle::make('dissemination')
                    ->required(),
                Select::make('classification_id')
                    ->relationship('classification', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('office_id')
                    ->relationship('office', 'name')
                    ->required(),
                Select::make('section_id')
                    ->relationship('section', 'name')
                    ->required(),
                Select::make('source_id')
                    ->relationship('source', 'name'),
                DateTimePicker::make('published_at'),
            ]);
    }
}
