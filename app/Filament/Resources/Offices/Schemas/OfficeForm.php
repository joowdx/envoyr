<?php

namespace App\Filament\Resources\Offices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name'),
                TextInput::make('acronym'),
                TextInput::make('head_name')
                    ->nullable(),
                TextInput::make('designation')
                    ->nullable(),
            ]);
    }
}
