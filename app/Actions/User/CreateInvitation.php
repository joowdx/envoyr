<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Str;

class CreateInvitation
{
    public function execute(string $email, UserRole $role, ?string $officeId, string $invitedBy, ?string $designation = null): User
    {
        return User::create([
            'email' => $email,
            'role' => $role,
            'office_id' => $officeId,
            'invited_by' => $invitedBy,
            'designation' => $designation,
            'invitation_token' => Str::random(64),
            'invitation_expires_at' => now()->addDays(7),
        ]);
    }
}
