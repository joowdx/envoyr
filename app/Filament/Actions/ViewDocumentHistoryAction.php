<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\ViewDocumentHistory;
use App\Models\Document;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class ViewDocumentHistoryAction extends Action
{
    use ViewDocumentHistory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('view-document-history');

        $this->label('History');

        $this->icon('heroicon-o-clock');

        $this->color('gray');

        $this->modalHeading('Document History');

        $this->modalContent(fn(Document $record) => $this->getDocumentHistory($record));

        $this->modalWidth('lg');
    }
}
