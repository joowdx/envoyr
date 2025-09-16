<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class TransmitDocumentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('transmit-document');

        $this->label('Transmit');

        $this->icon('heroicon-o-paper-airplane');

        $this->color('primary');

        $this->url(fn (Document $record): string => DocumentResource::getUrl('transmit', ['record' => $record]));

        $this->visible(
            fn (Document $record): bool => $record->isPublished() &&
                $this->canTransmitDocument($record) &&
                ! $record->activeTransmittal()->exists() &&
                ! $record->dissemination
        );
    }

    private function canTransmitDocument(Document $record): bool
    {
        $currentOfficeId = Auth::user()->office_id;
        
        if ($record->activeTransmittal) {
            return $record->activeTransmittal->to_office_id === $currentOfficeId;
        }
        
        $lastReceivedTransmittal = $record->transmittals()
            ->whereNotNull('received_at')
            ->orderBy('received_at', 'desc')
            ->first();
            
        if ($lastReceivedTransmittal) {
            return $lastReceivedTransmittal->to_office_id === $currentOfficeId;
        }
        
        return $record->office_id === $currentOfficeId;
    }
}
