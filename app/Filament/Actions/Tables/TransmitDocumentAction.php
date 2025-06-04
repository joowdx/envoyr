<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\TransmitDocument;
use Filament\Tables\Actions\Action;

class TransmitDocumentAction extends Action
{
    use TransmitDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootTransmitDocument();
    }
}
