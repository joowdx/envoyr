<?php

namespace App\Filament\Auth\Pages;

use App\Http\Responses\LoginResponse;
use Filament\Pages\Page;

class Redirect extends Page
{
    public function __construct()
    {
        $this->mount();
    }

    public function mount()
    {
        (new LoginResponse)->toResponse(request());
    }
}
