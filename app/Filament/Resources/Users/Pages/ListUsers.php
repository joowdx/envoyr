<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Invite User')
                ->modalHeading('Invite User')
                ->modalWidth('sm')
                ->icon('heroicon-o-paper-airplane')
                ->createAnother(false)
                ->successNotificationTitle(false)
                ->using(function (array $data): User {
                    $currentUser = Filament::auth()->user();


                    if ($currentUser->role !== UserRole::ROOT && ! $currentUser->office_id) {
                        throw new \Exception('You must be assigned to an office to invite users.');
                    }


                    $targetOfficeId = null;
                    if ($currentUser->role === UserRole::ROOT) {

                        $targetOfficeId = $data['office_id'] ?? null;
                    } else {

                        $targetOfficeId = $currentUser->office_id;
                    }

                    $invitation = User::createInvitation(
                        email: $data['email'],
                        role: UserRole::from($data['role']), 
                        officeId: $targetOfficeId,
                        invitedBy: $currentUser->id,
                        designation: $data['designation'] ?? null
                    );

                    UserResource::sendInvitationEmail($invitation);

                    return $invitation;
                }),
        ];
    }
}
