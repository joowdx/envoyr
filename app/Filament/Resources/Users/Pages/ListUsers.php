<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Invite User')
                ->modalHeading('Create User')
                ->modalWidth('sm')
                ->icon('heroicon-o-user-plus')
                ->createAnother(false)
                ->using(function (array $data): User {

                    $userData = UserResource::createUserWithOtp($data);
                    $otp = $userData['_otp'];
                    unset($userData['_otp']);

                    $user = User::create($userData);
                    UserResource::sendWelcomeEmail($user, $otp);

                    return $user;
                })
                ->successNotificationTitle(null),
        ];
    }
}
