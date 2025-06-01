<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateQR')
                ->label('QR')
                ->icon('heroicon-o-qr-code')
                ->modalWidth('md')
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
            Actions\DeleteAction::make(),
        ];
    }
}
