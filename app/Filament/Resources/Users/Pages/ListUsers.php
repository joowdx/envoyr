<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;

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
                ->using(function (array $data): User {
                    $currentUser = Filament::auth()->user();
                    
                    // ROOT users can invite to any office, others must be assigned to an office
                    if ($currentUser->role !== UserRole::ROOT && !$currentUser->office_id) {
                        throw new \Exception('You must be assigned to an office to invite users.');
                    }

                    // Determine which office to assign the invited user to
                    $targetOfficeId = null;
                    if ($currentUser->role === UserRole::ROOT) {
                        // ROOT users can specify an office, or leave it null if not provided
                        $targetOfficeId = $data['office_id'] ?? null;
                    } else {
                        // Non-ROOT users assign to their own office
                        $targetOfficeId = $currentUser->office_id;
                    }

                    $invitation = User::createInvitation(
                        email: $data['email'],
                        role: UserRole::from($data['role']), // Convert string to enum
                        officeId: $targetOfficeId,
                        invitedBy: $currentUser->id
                    );

                    UserResource::sendInvitationEmail($invitation);

                    return $invitation;
                })
                ->successNotificationTitle('Invitation Sent Successfully'),
        ];
    }
}
