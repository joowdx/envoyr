<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use App\Filament\Actions\ModifyAttachmentsAction;
use App\Filament\Actions\PublishDocumentAction;
use App\Filament\Actions\TransmitDocumentAction;
use App\Filament\Actions\UnpublishDocumentAction;
use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TransmitDocumentAction::make(),
            ModifyAttachmentsAction::make(),
            PublishDocumentAction::make(),
            UnpublishDocumentAction::make(),
            Actions\Action::make('generateQR')
                ->label('QR')
                ->icon('heroicon-o-qr-code')
                ->modalWidth('md')
                ->visible(fn (): bool => $this->record->isPublished())
                ->modalContent(function () {
                    $qrCode = (new GenerateQR)->__invoke($this->record->code);

                    return view('components.qr-code', [
                        'qrCode' => $qrCode,
                        'code' => $this->record->code,
                    ]);
                })
                ->modalFooterActions([
                    Actions\Action::make('download')
                        ->label('Download QR')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function () {
                            $base64 = (new DownloadQR)->__invoke($this->record);

                            return response()->streamDownload(
                                function () use ($base64) {
                                    echo base64_decode($base64);
                                },
                                'qr-code.pdf',
                                ['Content-Type' => 'application/pdf']
                            );
                        }),
                ]),
            EditAction::make()
                ->visible(fn (): bool => $this->record->isDraft() && $this->record->user_id === Auth::id()),
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->isDraft() && $this->record->user_id === Auth::id()),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\Documents\Widgets\TransmittalHistoryOverview::class,
            \App\Filament\Resources\Documents\Widgets\TransmittalHistoryTable::class,
        ];
    }
}
