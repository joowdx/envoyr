<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\TransmittalHistoryInfolist;
use Filament\Tables\Actions\Action;

class ViewDocumentHistoryAction extends Action
{
    use TransmittalHistoryInfolist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('view-document-history');

        $this->label('History');

        $this->icon('heroicon-o-clock');

        $this->modalHeading('Document History');

        $this->modalDescription('View the complete history of this document.');

        $this->modalIcon('heroicon-o-clock');

        $this->slideOver();

        $this->modalWidth('4xl');

        $this->infolist(self::getTransmittalHistorySchema());

        $this->modalSubmitAction(false);

        $this->modalCancelAction(false);
    }
}
