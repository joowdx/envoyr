<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\TransmitDocument;
use Filament\Actions\Action;

class TransmitDocumentAction extends Action
{
    use TransmitDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootTransmitDocument();
    }
}