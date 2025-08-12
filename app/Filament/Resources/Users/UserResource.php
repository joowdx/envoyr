<?php

namespace App\Filament\Resources\Users;

use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use App\Mail\UserFirstLoginOtpMail;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Filament\Support\Enums\Alignment;
use Filament\Notifications\Notification;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\Schemas\UserInfolist;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    // Changed signature to match parent (Schema)
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
        $otp = null;

        return UsersTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->label('New User')
                    ->modalHeading('Create User')
                    ->modalWidth('sm')
                    ->icon('heroicon-o-user-plus')
                    ->mutateFormDataUsing(function (array $data) use (&$otp) {
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
            ->actions([
                ViewAction::make()
                    ->modalHeading(fn (User $record) => "User: {$record->name}")
                    ->modalWidth('sm')
                    ->modalFooterActionsAlignment(Alignment::Center) 
                EditAction::make()
                    ->modalWidth('sm')
                    ->modalFooterActionsAlignment(Alignment::Center),
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
}
