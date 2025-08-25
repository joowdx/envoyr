<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Office;
use App\Models\User;

class OfficePolicy
{

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::ROOT,
            UserRole::ADMINISTRATOR,
            UserRole::LIAISON,
            UserRole::FRONT_DESK,
        ]);
    }

    // viewing
    public function view(User $user, Office $office): bool
    {
        if ($user->role === UserRole::ROOT) {
            return true;
        }
    
        // Both ADMINISTRATOR and other roles follow the same rule
        return $user->office_id === $office->id;
    }


    // creating
    public function create(User $user): bool
    {
        return $user->role === UserRole::ROOT;
    }

    // updating
    public function update(User $user, Office $office): bool
    {
        if ($user->role === UserRole::ROOT) {
            return true;
        }

        if ($user->role === UserRole::ADMINISTRATOR) {
            return $user->office_id === $office->id;
        }

        return false;
    }

    // deleting
    public function delete(User $user, Office $office): bool
    {
        return $user->role === UserRole::ROOT;
    }

    public function restore(User $user, Office $office): bool
    {
        return $user->role === UserRole::ROOT;
    }

    public function forceDelete(User $user, Office $office): bool
    {
        return $user->role === UserRole::ROOT;
    }

    public function __construct()
    {
        //
    }
}
