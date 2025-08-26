<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Office;
use App\Models\User;

class OfficePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->role === UserRole::ROOT) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::ROOT,
            UserRole::ADMINISTRATOR,
            UserRole::LIAISON,
            UserRole::FRONT_DESK,
        ]);
    }

    public function view(User $user, Office $office): bool
    {
        if ($user->role === UserRole::ROOT) {
            return true;
        }

        return $user->office_id === $office->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

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

    public function delete(User $user, Office $office): bool
    {
        return false;
    }

    public function restore(User $user, Office $office): bool
    {
        return false;
    }

    public function forceDelete(User $user, Office $office): bool
    {
        return false;
    }

    public function __construct()
    {
        //
    }
}
