<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        ImageEntry::make('avatar')
                            ->label('Avatar')
                            ->circular()
                            ->defaultImageUrl(url('/images/placeholder.png'))
                            ->columnSpanFull(),
                        TextEntry::make('name')
                            ->label('Full Name'),
                        TextEntry::make('email')
                            ->label('Email Address')
                            ->copyable(),
                        TextEntry::make('role')
                            ->label('Role')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->value)
                            ->color(fn ($state): string => match ($state->value) {
                                'ROOT' => 'danger',
                                'ADMINISTRATOR' => 'warning',
                                'LIASON' => 'info',
                                'USER' => 'success',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make('Organization Details')
                    ->schema([
                        TextEntry::make('office.name')
                            ->label('Office')
                            ->url(fn ($record) => $record->office ? route('filament.app.resources.offices.view', ['record' => $record->office_id]) : null)
                            ->placeholder('No office assigned'),
                        TextEntry::make('section.name')
                            ->label('Section')
                            ->url(fn ($record) => $record->section ? route('filament.app.resources.sections.view', ['record' => $record->section_id]) : null)
                            ->placeholder('No section assigned'),
                        TextEntry::make('designation')
                            ->label('Designation')
                            ->placeholder('No designation provided'),
                    ])
                    ->columns(2),

                Section::make('Account Information')
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime()
                            ->placeholder('Not verified'),
                        TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                        TextEntry::make('deleted_at')
                            ->label('Deactivated At')
                            ->dateTime()
                            ->placeholder('Active')
                            ->visible(fn ($record) => $record->deleted_at !== null),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(function () {
                    $user = Auth::user();

                    // ROOT users can edit any user
                    if ($user->role->value === 'ROOT') {
                        return true;
                    }

                    // ADMINISTRATOR users can edit users in their office (except ROOT users)
                    if ($user->role->value === 'ADMINISTRATOR') {
                        return $user->office_id === $this->record->office_id &&
                               $this->record->role->value !== 'ROOT';
                    }

                    // Users can edit their own profile
                    return $user->id === $this->record->id;
                }),

            Actions\DeleteAction::make()
                ->visible(function () {
                    $user = Auth::user();

                    // ROOT users can delete any user (except themselves)
                    if ($user->role->value === 'ROOT') {
                        return $user->id !== $this->record->id;
                    }

                    // ADMINISTRATOR users can delete users in their office (except ROOT users and themselves)
                    if ($user->role->value === 'ADMINISTRATOR') {
                        return $user->office_id === $this->record->office_id &&
                               $this->record->role->value !== 'ROOT' &&
                               $user->id !== $this->record->id;
                    }

                    return false;
                })
                ->requiresConfirmation()
                ->modalHeading('Deactivate User')
                ->modalDescription('Are you sure you want to deactivate this user? They will no longer be able to access the system.')
                ->modalSubmitActionLabel('Yes, deactivate'),

            Actions\RestoreAction::make()
                ->visible(function () {
                    $user = Auth::user();

                    // Only show restore action for soft-deleted records
                    if (! $this->record->trashed()) {
                        return false;
                    }

                    // ROOT users can restore any user
                    if ($user->role->value === 'ROOT') {
                        return true;
                    }

                    // ADMINISTRATOR users can restore users in their office
                    if ($user->role->value === 'ADMINISTRATOR') {
                        return $user->office_id === $this->record->office_id;
                    }

                    return false;
                }),
        ];
    }
}
