<?php

namespace App\Providers;

use Filament\Forms\Components\Select;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Filters\SelectFilter;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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

        Select::configureUsing(fn (Select $component) => $component->native(false));

        SelectFilter::configureUsing(fn (SelectFilter $component) => $component->native(false));
    }
}
