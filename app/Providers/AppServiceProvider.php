<?php

namespace App\Providers;

use App\Models\Office;
use App\Policies\OfficePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Filament\Support\Facades\FilamentView;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn () => Blade::render('@vite(\'resources/css/app.css\')'));
        Gate::policy(Office::class, OfficePolicy::class);
    }
}
