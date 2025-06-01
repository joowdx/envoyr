<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Actions\ReceiveDocumentAction;
use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ReceiveDocumentAction::make(),
        ];
    }
}
