<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Mail\UserFirstLoginOtpMail;
use App\Models\User;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
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
                    ->infolist(function ($schema) { // accept the passed Schema instead of Infolist
                        return $schema
                            ->columns(1)
                            ->components([
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->alignment(Alignment::Center),
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->alignment(Alignment::Center),
                                TextEntry::make('role')
                                    ->label('Role')
                                    ->badge()
                                    ->alignment(Alignment::Center),
                                TextEntry::make('office.name')
                                    ->label('Office')
                                    ->placeholder('—')
                                    ->alignment(Alignment::Center),
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->alignment(Alignment::Center),
                                TextEntry::make('updated_at')
                                    ->label('Updated')
                                    ->since()
                                    ->alignment(Alignment::Center),
                            ]);
                    }),
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
