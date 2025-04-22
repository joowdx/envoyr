<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Http\Middleware\Approve;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Route;
use App\Filament\Auth\Pages\Registration;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Panels\Auth\Pages\Approval;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Panels\Auth\Pages\Verification;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Auth\Controllers\EmailVerificationController;
use App\Filament\Auth\Pages\Redirect;

class AuthPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('auth')
            ->path('auth')
            ->login()
            ->registration(Registration::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Auth/Resources'), for: 'App\\Filament\\Auth\\Resources')
            ->discoverPages(in: app_path('Filament/Auth/Pages'), for: 'App\\Filament\\Auth\\Pages')
            ->discoverWidgets(in: app_path('Filament/Auth/Widgets'), for: 'App\\Filament\\Auth\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->pages([
                Redirect::class,
                Verification::class,
                Approval::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);          
    }
    public function boot(): void
    {
        Route::middleware('web')->group(
            fn () => Route::get('/auth/email-verification/verify/{id}/{hash}', EmailVerificationController::class)
                ->name('filament.auth.auth.email-verification.verify')
        );
    }
}
