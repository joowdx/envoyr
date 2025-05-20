<?php

namespace App\Filament\User\Resources\DocumentResource\Pages;

use App\Actions\GenerateQR;
use App\Actions\ViewQR;
use App\Filament\User\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('generateQR')
            //     ->label('Generate QR')
            //     ->icon('heroicon-o-qr-code')
            //     ->modalWidth('md')
            //     ->action(function () {
            //         $qrCode = (new GenerateQR())->__invoke($this->record->code, [
            //             'title' => $this->record->title,
            //             'classification' => $this->record->classification?->name,
            //         ]);
            //     }),
            Actions\DeleteAction::make(),
        ];
    }
}
