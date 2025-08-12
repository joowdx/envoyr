<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Full Name')
                    ->placeholder('Enter full name')
                    // ->autofocus()
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

                // Select::make('role')
                //     ->label('Role')
                //     ->options([
                //         'admin' => 'Admin',
                //         'user' => 'User',
                //     ])
                //     ->default('user')
                //     ->required()
                //     ->columnSpan(1),

                // Select::make('office_id')
                //     ->label('Office')
                //     ->relationship('office', 'name')
                //     ->searchable()
                //     ->required()
                //     ->columnSpan(1),

            ]);
    }
}
