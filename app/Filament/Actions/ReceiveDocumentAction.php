<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\ReceiveDocument;
use Filament\Actions\Action;

class ReceiveDocumentAction extends Action
{
    use ReceiveDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootReceiveDocument();
    }
}
