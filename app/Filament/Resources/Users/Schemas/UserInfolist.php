<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),

                        TextEntry::make('role')
                            ->label('Role')
                            ->formatStateUsing(fn ($state) => strtoupper($state->value))
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                \App\Enums\UserRole::ROOT => 'danger',
                                \App\Enums\UserRole::ADMINISTRATOR => 'warning',
                                \App\Enums\UserRole::LIAISON => 'info',
                                \App\Enums\UserRole::FRONT_DESK => 'success',
                                \App\Enums\UserRole::USER => 'gray',
                            }),

                        TextEntry::make('designation')
                            ->label('Designation')
                            ->placeholder('Not specified'),

                        TextEntry::make('office.name')
                            ->label('Office')
                            ->placeholder('No office assigned'),

                        TextEntry::make('section.name')
                            ->label('Section')
                            ->placeholder('No section assigned'),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Email Status')
                            ->formatStateUsing(function ($state, $record) {
                                // Email is verified upon completing registration
                                if ($record->invitation_accepted_at) {
                                    return '✓ Verified';
                                }
                                return '✗ Unverified';
                            })
                            ->color(function ($state, $record) {
                                return $record->invitation_accepted_at ? 'success' : 'danger';
                            }),

                        TextEntry::make('invitation_accepted_at')
                            ->label('Account Status')
                            ->formatStateUsing(function ($state, $record) {
                                // Check if deactivated first
                                if ($record->deactivated_at) {
                                    return '🚫 Deactivated';
                                }
                                
                                // Then check if pending invitation
                                if ($record->isPendingInvitation()) {
                                    return '⏳ Pending';
                                }

                                return '✓ Active';
                            })
                            ->color(function ($state, $record) {
                                if ($record->deactivated_at) {
                                    return 'gray';
                                }
                                return $record->isPendingInvitation() ? 'warning' : 'success';
                            }),

                        TextEntry::make('created_at')
                            ->label('Joined')
                            ->since(),
                    ])
                    ->columns(3),

                // Show deactivation details if user is deactivated
                Section::make('Deactivation Details')
                    ->schema([
                        TextEntry::make('deactivated_at')
                            ->label('Deactivated On')
                            ->dateTime()
                            ->color('danger'),

                        TextEntry::make('deactivatedByUser.name')
                            ->label('Deactivated By')
                            ->icon('heroicon-o-user-minus')
                            ->color('danger')
                            ->placeholder('System'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->deactivated_at !== null),

                Section::make('Invitation')
                    ->schema([
                        TextEntry::make('invitedBy.name')
                            ->label('Invited by')
                            ->icon('heroicon-o-user')
                            ->placeholder('N/A'),

                        TextEntry::make('invitation_expires_at')
                            ->label('Invitation Expires')
                            ->since()
                            ->color('warning')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2)
                    ->collapsed(false),
            ]);
    }
}
