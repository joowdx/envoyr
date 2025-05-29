<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;

class RequiredOffice extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string $view = 'filament.user.pages.required-office';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $layout = 'filament-panels::components.layout.base';

    public static function getRelativeRouteName(): string
    {
        return 'required-office';
    }

    public function getTitle(): string
    {
        return 'Access Denied';
    }

    public function getHeading(): string
    {
        return 'Access Denied';
    }

    public function getSubheading(): ?string
    {
        return 'You must be associated with an office to access this page.';
    }
}
