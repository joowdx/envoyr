<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Filament\Resources\Enums\UserRole;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        /** @var User $user */
        $user = $request->user();

        $route = match ($user->role) {
            UserRole::ROOT, UserRole::ADMINISTRATOR => 'filament.admin.pages.dashboard',
            // UserRole::LIAISON => 'filament.liaison.pages.dashboard',
            // UserRole::RECEIVER => 'filament.receiver.pages.dashboard',
            // UserRole::USER => 'filament.user.pages.dashboard',
            default => 'filament.home.pages.',
        };

        return redirect()->route($route);
    }
}
