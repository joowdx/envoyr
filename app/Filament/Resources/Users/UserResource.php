<?php

namespace App\Filament\Resources\Users;

use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use App\Mail\UserFirstLoginOtpMail;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\Schemas\UserInfolist;

class UserResource extends Resource
{
    private const OTP_LENGTH = 6;

    private const OTP_EXPIRY_HOURS = 24;

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
            ->headerActions([
                CreateAction::make()
                    ->label('New User')
                    ->modalHeading('Create User')
                    ->modalWidth('sm')
                    ->icon('heroicon-o-user-plus')
                    ->mutateDataUsing(function (array $data) use (&$otp) {
                        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $data['password'] = Hash::make($otp);
                        $data['force_password_reset'] = true;

                        return $data;
                    })
                    ->createAnother(false)
                    ->after(function (User $record) use (&$otp) {
                        Mail::to($record->email)->send(new UserFirstLoginOtpMail($otp));
                        Notification::make()
                            ->title('User created')
                            ->body('One-time login code emailed.')
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle(null),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn (User $record) => "User: {$record->name}")
                    ->modalWidth('sm'),

                EditAction::make()
                    ->modalWidth('sm'),

                self::resendOtpAction(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Users\Pages\ListUsers::route('/'),
        ];
    }

    // Extracted action for better readability
    private static function resendOtpAction(): Action
    {
        return Action::make('resendOtp')
            ->label('Resend OTP')
            ->icon('heroicon-o-envelope')
            ->color('warning')
            ->visible(fn (User $record) => $record->needsPasswordReset())
            ->requiresConfirmation()
            ->modalHeading('Resend OTP Code')
            ->modalDescription('Generate a new one-time password and send it to the user\'s email.')
            ->action(fn (User $record) => self::handleResendOtp($record));
    }

    // Clean action handler
    private static function handleResendOtp(User $record): void
    {
        try {
            $otp = self::generateSecureOtp();

            $record->update([
                'password' => Hash::make($otp),
                'password_reset_at' => null,
                'otp_expires_at' => now()->addHours(self::OTP_EXPIRY_HOURS),
            ]);

            Mail::to($record->email)->send(new UserFirstLoginOtpMail($otp));

            Notification::make()
                ->title('OTP Sent Successfully')
                ->body("New OTP sent to {$record->email} (expires in ".self::OTP_EXPIRY_HOURS.' hours)')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send OTP')
                ->body('Please try again or contact support if the issue persists.')
                ->danger()
                ->send();
        }
    }

    public static function generateSecureOtp(): string
    {
        $min = 10 ** (self::OTP_LENGTH - 1);
        $max = (10 ** self::OTP_LENGTH) - 1;

        return (string) random_int($min, $max);
    }

    public static function createUserWithOtp(array $data): array
    {
        $otp = self::generateSecureOtp();

        return array_merge($data, [
            'password' => Hash::make($otp),
            'password_reset_at' => null,
            'otp_expires_at' => now()->addHours(self::OTP_EXPIRY_HOURS),
            '_otp' => $otp, // For immediate use after creation
        ]);
    }

    public static function sendWelcomeEmail(User $user, string $otp): void
    {
        try {
            Mail::to($user->email)->send(new UserFirstLoginOtpMail($otp));

            Notification::make()
                ->title('User Created Successfully')
                ->body("Welcome email sent to {$user->email}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('User Created')
                ->body('User created successfully, but email delivery failed. Please resend OTP manually.')
                ->warning()
                ->send();
        }
    }
}
