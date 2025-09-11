<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use App\Models\Transmittal;
use Infolists\Components\Tabs\Tab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;


trait TransmittalHistoryInfolist
{
    protected static function getTransmittalHistorySchema(): array
    {
        return [
            Components\Section::make('Document Transmittal History')
                ->description('View the complete transmission history and attachment versions for this document.')
                        ->schema([
                    Components\Placeholder::make('current_transmittal')
                        ->label('Current Status')
                        ->content(function ($record) {
                            $activeTransmittal = $record->activeTransmittal;
                            if (!$activeTransmittal) {
                                return 'No active transmittal';
                            }
                            
                            $status = $activeTransmittal->received_at ? 'Received' : 'In Transit';
                            $office = $activeTransmittal->toOffice->name ?? 'Unknown Office';
                            
                            return "Status: {$status} | To: {$office}";
                        })
                        ->visible(fn ($record) => $record->activeTransmittal()->exists()),
                        
                    Components\Placeholder::make('transmittal_count')
                        ->label('Total Transmittals')
                        ->content(fn ($record) => $record->transmittals->count() . ' transmittal(s)')
                        ->visible(fn ($record) => $record->transmittals->isNotEmpty()),
                        
                    Components\Placeholder::make('attachment_summary')
                        ->label('Current Attachments')
                        ->content(function ($record) {
                            $draftAttachment = $record->attachment;
                            if (!$draftAttachment || $draftAttachment->contents->isEmpty()) {
                                return 'No attachments';
                            }
                            
                            $fileCount = $draftAttachment->contents->count();
                            $fileNames = $draftAttachment->contents->pluck('title')->join(', ');
                            
                            return "{$fileCount} file(s): {$fileNames}";
                        }),
                ])
                ->collapsible()
                ->persistCollapsed(),
                
            Components\Section::make('Transmittal History')
                ->description('Detailed history of all transmittals for this document.')
                        ->schema([
                    Components\Placeholder::make('transmittal_history')
                        ->label('')
                        ->content(function ($record) {
                            if ($record->transmittals->isEmpty()) {
                                return 'No transmittals yet.';
                            }
                            
                            $history = '';
                            foreach ($record->transmittals as $index => $transmittal) {
                                $num = $index + 1;
                                $from = $transmittal->fromOffice->name ?? 'Unknown';
                                $to = $transmittal->toOffice->name ?? 'Unknown';
                                $date = $transmittal->created_at->format('M d, Y H:i');
                                $status = $transmittal->received_at ? 'Received' : 'Pending';
                                $attachmentCount = $transmittal->attachments->sum(fn($att) => $att->contents->count());
                                
                                $history .= "#{$num}: {$from} → {$to} ({$date}) - {$status} - {$attachmentCount} files\n";
                                $history .= "Purpose: {$transmittal->purpose}\n";
                                if ($transmittal->remarks) {
                                    $history .= "Remarks: {$transmittal->remarks}\n";
                                }
                                $history .= "\n";
                            }
                            
                            return trim($history);
                        })
                        ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace;']),
                ])
                ->collapsible()
                ->collapsed()
                ->visible(fn ($record) => $record->transmittals->isNotEmpty()),
        ];
    }
}
