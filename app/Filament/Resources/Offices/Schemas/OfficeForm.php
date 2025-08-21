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
                TextInput::make('name')
                    ->required()
                    ->label('Office Name'),
                TextInput::make('acronym')
                    ->required()
                    ->label('Office Acronym'),
                TextInput::make('head_name')
                    ->label('Head Name'),   
                TextInput::make('designation')
                    ->label('Designation'),
            ]);
    }
}
