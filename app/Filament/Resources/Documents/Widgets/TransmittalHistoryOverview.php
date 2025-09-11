<?php

namespace App\Filament\Resources\Documents\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransmittalHistoryOverview extends BaseWidget
{
    public ?Document $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $activeTransmittal = $this->record->activeTransmittal;
        $transmittalsCount = $this->record->transmittals->count();
        $draftAttachment = $this->record->attachment;
        $attachmentCount = $draftAttachment?->contents?->count() ?? 0;

        return [
            Stat::make('Document Status', $this->record->isPublished() ? 'Published' : 'Draft')
                ->description($this->record->isPublished() ? 'Document is published and can be transmitted' : 'Document is still in draft mode')
                ->icon('heroicon-o-document-text')
                ->color($this->record->isPublished() ? 'success' : 'warning'),

            Stat::make('Active Transmittal', $activeTransmittal ? 'In Progress' : 'None')
                ->description($activeTransmittal ? 
                    'To: ' . ($activeTransmittal->toOffice->name ?? 'Unknown Office') : 
                    'No active transmittals'
                )
                ->icon('heroicon-o-paper-airplane')
                ->color($activeTransmittal ? 'info' : 'gray'),

            Stat::make('Total Transmittals', $transmittalsCount)
                ->description($transmittalsCount > 0 ? 'Document has been transmitted' : 'Never transmitted')
                ->icon('heroicon-o-arrow-path')
                ->color($transmittalsCount > 0 ? 'success' : 'gray'),

            Stat::make('Current Attachments', $attachmentCount)
                ->description($attachmentCount > 0 ? 
                    $draftAttachment->contents->pluck('title')->join(', ') : 
                    'No attachments'
                )
                ->icon('heroicon-o-paper-clip')
                ->color($attachmentCount > 0 ? 'success' : 'gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
}
