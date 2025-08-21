<?php

namespace App\Filament\Resources\Offices\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

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
