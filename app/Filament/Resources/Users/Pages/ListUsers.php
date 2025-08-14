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
                ->label('New User')
                ->modalHeading('Create User')
                ->modalWidth('sm')
                ->icon('heroicon-o-user-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    return UserResource::prepareUserData($data);
                })
                ->createAnother(false)
                ->using(function (array $data): User {
                    $otp = $data['_otp'];
                    unset($data['_otp']);
                    
                    $user = User::create($data);
                    UserResource::sendWelcomeEmail($user, $otp);
                    
                    return $user;
                })
                ->successNotificationTitle(null),
        ];
    }
}
