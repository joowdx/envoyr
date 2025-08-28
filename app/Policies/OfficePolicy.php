<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Office;
use App\Models\User;

class OfficePolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === UserRole::ROOT ?: null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Office $office): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Office $office): bool
    {
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
}
