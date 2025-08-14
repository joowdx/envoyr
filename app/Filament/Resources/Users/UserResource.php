<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Mail\UserFirstLoginOtpMail;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
        return UsersTable::configure($table)
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn (User $record) => "User: {$record->name}")
                    ->modalWidth('sm'),
                EditAction::make()
                    ->modalWidth('sm'),
                Action::make('resendOtp')
                    ->label('Resend OTP')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->visible(fn (User $record) => $record->force_password_reset ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Resend OTP Code')
                    ->modalDescription('This will generate a new one-time password and send it to the user\'s email.')
                    ->action(function (User $record) {
                        $otp = self::generateOtp();
                        $record->update(['password' => Hash::make($otp)]);
                        self::sendWelcomeEmail($record, $otp);

                        Notification::make()
                            ->title('OTP Resent')
                            ->body("New OTP sent to {$record->email}")
                            ->success()
                            ->send();
                    }),
            ]);
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

    /**
     * Generate a 6-digit OTP for user registration
     */
    public static function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send welcome email with OTP to new user
     */
    public static function sendWelcomeEmail(User $user, string $otp): void
    {
        Mail::to($user->email)->send(new UserFirstLoginOtpMail($otp));

        Notification::make()
            ->title('OTP Sent')
            ->body('One-time login code emailed.')
            ->success()
            ->send();
    }

    /**
     * Prepare user data for creation with OTP password
     */
    public static function prepareUserData(array $data): array
    {
        $otp = self::generateOtp();

        return [
            ...$data,
            'password' => Hash::make($otp),
            'force_password_reset' => true,
            '_otp' => $otp,
        ];
    }
}
