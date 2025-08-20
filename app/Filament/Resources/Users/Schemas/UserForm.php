<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Select::make('role')
                    ->label('Role')
                    ->options(UserRole::options())
                    ->default(UserRole::USER->value)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('Enter email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('A one-time login code will be sent to this email.')
                    ->columnSpan(1),

            ]);
    }
}
