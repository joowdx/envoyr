<?php

namespace App\Filament\Actions\Tables;

use App\Models\Document;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use App\Filament\Actions\Concerns\ReceiveDocument;

class ReceiveDocumentAction extends Action
{
    use ReceiveDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootReceiveDocument();

        $this->visible(function (?Document $record): bool {
            if (! $record) {
                return false;
            }

            return $record->activeTransmittal &&
                $record->activeTransmittal->to_office_id === Auth::user()->office_id;
        });
    }
}