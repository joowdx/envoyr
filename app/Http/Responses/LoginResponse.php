<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

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
            UserRole::USER => 'filament.user.pages.dashboard',
            default => abort(403),
        };

        return redirect()->route($route);
    }
}
