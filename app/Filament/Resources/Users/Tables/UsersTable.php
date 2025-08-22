<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Users\UserResource;
use Illuminate\Database\Eloquent\Builder;
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
                    
                TextColumn::make('invitation_status')
                    ->label('Status')
                    ->formatStateUsing(function ($record) {
                        if ($record->isPendingInvitation()) {
                            return $record->isInvitationExpired() ? '⏰ Expired' : '⏳ Pending';
                        }
                        return $record->deactivated_at ? '❌ Deactivated' : '✅ Active';
                    })
                    ->color(function ($record) {
                        if ($record->isPendingInvitation()) {
                            return $record->isInvitationExpired() ? 'danger' : 'warning';
                        }
                        return $record->deactivated_at ? 'danger' : 'success';
                    })
                    ->badge()
                    ->sortable(),
                    
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
                // Tabs are now handled in ListUsers page
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
                ViewAction::make()
                    ->modalWidth('md'),
                    
                EditAction::make()
                    ->modalWidth('sm'),
                    
                Action::make('resend_invitation')
                    ->label('Resend Invitation')
                    ->icon('heroicon-o-paper-airplane')
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
                            'invitation_expires_at' => now()->addDays(7)
                        ]);
                    })
                    ->successNotification(
                        fn ($record) => \Filament\Notifications\Notification::make()
                            ->title('Invitation Resent')
                            ->body("Registration link has been resent to {$record->email}")
                            ->success()
                    ),
            ])
            ->toolbarActions([
                    BulkAction::make('resend_invitations')
                        ->label('Resend Invitations')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Resend Invitations')
                        ->modalDescription('Are you sure you want to resend invitations to all selected users with pending invitations?')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->isPendingInvitation()) {
                                    // Generate new registration URL and send email
                                    UserResource::sendInvitationEmail($record);

                                    // Update invitation expiration (extend by 7 days)
                                    $record->update([
                                        'invitation_expires_at' => now()->addDays(7)
                                    ]);

                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Invitations Resent')
                                ->body("Successfully resent {$count} invitation(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    ]);
    }
}
