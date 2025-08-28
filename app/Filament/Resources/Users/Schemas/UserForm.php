<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\Office;
use Filament\Facades\Filament;
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
                    ->helperText(function () {
                        $currentUser = Filament::auth()->user();
                        if ($currentUser->role === UserRole::ROOT) {
                            return 'ROOT user: You can assign users to any office, or leave office unassigned.';
                        } else {
                            $office = $currentUser->office->name ?? 'No office assigned';

                            return "User will be invited to: {$office}. A registration link will be sent to this email.";
                        }
                    })
                    ->columnSpan(1),

                Select::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->options(Office::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpan(1),

                TextInput::make('designation')
                    ->label('Designation')
                    ->placeholder('Enter user designation')
                    ->nullable()
                    ->helperText('Leave blank to let the user set their own designation')
                    ->columnSpan(1),

            ]);
    }
}
