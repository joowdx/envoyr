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
    protected function getTableQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = User::query();
        if ($user->role !== UserRole::ROOT) {
            $query->where('office_id', $user->office_id);
        }
        // Exclude the current user from the list
        $query->where('id', '!=', $user->id);
        return $query;
    }
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        $user = Filament::auth()->user();
        $baseQuery = User::query();
        if ($user->role !== UserRole::ROOT) {
            $baseQuery->where('office_id', $user->office_id);
        }
        $baseQuery->where('id', '!=', $user->id);

        return [
            'all' => Tab::make('All Users')
                ->icon('heroicon-o-users')
                ->badge((clone $baseQuery)->count()),

            'active' => Tab::make('Active Users')
                ->icon('heroicon-o-check-circle')
                ->badge((clone $baseQuery)
                    ->whereNotNull('invitation_accepted_at')
                    ->whereNull('deactivated_at')
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotNull('invitation_accepted_at')
                    ->whereNull('deactivated_at')
                ),

            'pending' => Tab::make('Pending Invitation')
                ->icon('heroicon-o-clock')
                ->badge((clone $baseQuery)
                    ->whereNull('invitation_accepted_at')
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
                ->badge((clone $baseQuery)
                    ->whereNotNull('deactivated_at')
                    ->count())
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

                    $invitation = app(\App\Actions\User\CreateInvitation::class)->execute(
                        $data['email'],
                        UserRole::from($data['role']),
                        $targetOfficeId,
                        $currentUser->id,
                        $data['designation'] ?? null
                    );

                    UserResource::sendInvitationEmail($invitation);

                    return $invitation;
                }),
        ];
    }
}
