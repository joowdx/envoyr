<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('office.name')
                    ->label('Office')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn ($state) => strtoupper($state->value))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->deactivated_at) {
                            return 'Deactivated';
                        }
                        if ($record->isPendingInvitation()) {
                            return 'Pending';
                        }
                        return 'Active';
                    })
                    ->color(fn ($state) => $state === 'Deactivated' ? 'gray' : ($state === 'Pending' ? 'warning' : 'success')),

                TextColumn::make('designation')
                    ->label('Designation')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'root' => 'ROOT',
                        'administrator' => 'ADMINISTRATOR',
                        'liaison' => 'LIAISON',
                        'front_desk' => 'FRONT DESK',
                        'user' => 'USER',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->tooltip('View')
                    ->modalWidth('md'),

                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->tooltip('Edit')
                    ->modalWidth('sm'),

                Action::make('resend_invitation')
                    ->icon('heroicon-o-paper-airplane')
                    ->tooltip('Resend Invitation')
                    ->color('warning')
                    ->visible(fn ($record) => $record->isPendingInvitation())
                    ->requiresConfirmation()
                    ->modalHeading('Resend Invitation')
                    ->modalDescription(fn ($record) => "Are you sure you want to resend the invitation to {$record->email}?")
                    ->action(function ($record) {
                        // Generate new registration URL and send email
                        UserResource::sendInvitationEmail($record);

                        // Update invitation expiration (extend by 7 days)
                        $record->update([
                            'invitation_expires_at' => now()->addDays(7),
                        ]);
                    })
                    ->successNotification(
                        fn ($record) => \Filament\Notifications\Notification::make()
                            ->title('Invitation Resent')
                            ->body("Registration link has been resent to {$record->email}")
                            ->success()
                    ),

                Action::make('deactivate')
                    ->icon('heroicon-o-user-minus')
                    ->tooltip('Deactivate')
                    ->color('danger')
                    ->visible(fn ($record) => !$record->deactivated_at && in_array(\Filament\Facades\Filament::auth()->user()?->role->value, ['root', 'administrator']))
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate User')
                    ->modalDescription(fn ($record) => "Are you sure you want to deactivate {$record->name} ({$record->email})?")
                    ->action(function ($record) {
                        $currentUser = \Filament\Facades\Filament::auth()->user();
                        $record->deactivate($currentUser);
                    })
                    ->successNotification(
                        fn ($record) => \Filament\Notifications\Notification::make()
                            ->title('User Deactivated')
                            ->body("{$record->name} has been deactivated.")
                            ->success()
                    ),

                Action::make('reactivate')
                    ->icon('heroicon-o-user-plus')
                    ->tooltip('Reactivate')
                    ->color('success')
                    ->visible(fn ($record) => $record->deactivated_at && in_array(\Filament\Facades\Filament::auth()->user()?->role->value, ['root', 'administrator']))
                    ->requiresConfirmation()
                    ->modalHeading('Reactivate User')
                    ->modalDescription(fn ($record) => "Are you sure you want to reactivate {$record->name} ({$record->email})?")
                    ->action(function ($record) {
                        $record->update([
                            'deactivated_at' => null,
                            'deactivated_by' => null,
                        ]);
                    })
                    ->successNotification(
                        fn ($record) => \Filament\Notifications\Notification::make()
                            ->title('User Reactivated')
                            ->body("{$record->name} has been reactivated.")
                            ->success()
                    ),
            ])
        ]);

    }
}
