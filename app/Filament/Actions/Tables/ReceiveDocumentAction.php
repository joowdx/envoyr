<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\ReceiveDocument;
use Filament\Tables\Actions\Action;

class ReceiveDocumentAction extends Action
{
    use ReceiveDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootReceiveDocument();
    }
}