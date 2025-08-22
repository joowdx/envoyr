<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users')
                ->icon('heroicon-o-users')
                ->badge(User::count()),

            'active' => Tab::make('Active Users')
                ->icon('heroicon-o-check-circle')
                ->badge(User::whereNotNull('invitation_accepted_at')
                    ->whereNull('deactivated_at')
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotNull('invitation_accepted_at')
                    ->whereNull('deactivated_at')
                ),

            'pending' => Tab::make('Pending Invitation')
                ->icon('heroicon-o-clock')
                ->badge(User::whereNull('invitation_accepted_at')
                    ->whereNotNull('invitation_token')
                    ->where(function (Builder $query) {
                        $query->whereNull('invitation_expires_at')
                              ->orWhere('invitation_expires_at', '>', now());
                    })
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNull('invitation_accepted_at')
                    ->whereNotNull('invitation_token')
                    ->where(function (Builder $query) {
                        $query->whereNull('invitation_expires_at')
                              ->orWhere('invitation_expires_at', '>', now());
                    })
                ),

            'deactivated' => Tab::make('Deactivated Users')
                ->icon('heroicon-o-x-circle')
                ->badge(User::whereNotNull('deactivated_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotNull('deactivated_at')
                ),
        ];
    }

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
