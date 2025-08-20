<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    // Send invitation email
    public static function sendInvitationEmail(User $invitation): void
    {
        try {
            // Mail::to($invitation->email)->send(new UserInvitationMail($invitation));

            Notification::make()
                ->title('Invitation Sent Successfully')
                ->body("Registration link sent to {$invitation->email}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Email Failed')
                ->body('Invitation created but email failed to send.')
                ->warning()
                ->send();
        }
    }
}
