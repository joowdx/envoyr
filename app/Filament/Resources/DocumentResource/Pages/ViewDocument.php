<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use App\Filament\Actions\PublishAction;
use App\Filament\Actions\TransmitDocumentAction;
use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TransmitDocumentAction::make(),
            PublishAction::make()
                ->visible(fn (): bool => $this->record->isDraft() && $this->record->user_id === Auth::id()),
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
            Actions\EditAction::make()
                ->visible(fn (): bool => $this->record->isDraft() && $this->record->user_id === Auth::id()),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->record->user_id === Auth::id()),
        ];
    }
}
