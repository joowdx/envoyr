<?php

namespace App\Filament\Auth\Pages;

use Filament\Facades\Filament;
use Filament\Pages\SimplePage;
use App\Http\Middleware\Verify;
use App\Http\Middleware\Authenticate;
use App\Http\Responses\LoginResponse;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Auth\Concerns\BaseAuthPage;
use Illuminate\Routing\Controllers\HasMiddleware;

class Approval extends SimplePage implements HasMiddleware
{
    use BaseAuthPage;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.auth.pages.approval';

    public static function getSlug(): string
    {
        return 'account-approval/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.account-approval.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
            Verify::class,
        ];
    }

    public function mount(): void
    {
        /** @var User */
        $user = Filament::auth()->user();

        if ($user->hasApprovedAccount()) {
            (new LoginResponse)->toResponse(request());
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Account review in progress';
    }
}
