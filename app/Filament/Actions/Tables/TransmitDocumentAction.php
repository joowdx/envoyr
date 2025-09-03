<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\Action;
use App\Filament\Actions\Concerns\TransmitDocument;

class TransmitDocumentAction extends Action
{
    use TransmitDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootTransmitDocument();
    }
}