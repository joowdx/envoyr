<?php

namespace App\Filament\Auth\Pages;

use App\Filament\Auth\Concerns\BaseAuthPage;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use Illuminate\Routing\Controllers\HasMiddleware;

class Verification extends EmailVerificationPrompt implements HasMiddleware
{
    use BaseAuthPage;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.auth.pages.verification';

    public static function getSlug(): string
    {
        return 'email-verification/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.email-verification.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
        ];
    }

    public function mount(): void
    {
        /** @var User */
        $user = Filament::auth()->user();

        if ($user->hasVerifiedEmail()) {
            (new LoginResponse)->toResponse(request());
        }
    }
}
