<?php

namespace App\Filament\Resources\SourceResource\Pages;
use App\Filament\Resources\SourceResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;





class ViewSources extends ViewRecord
{
    protected static string $resource = SourceResource::class;
    public function getTitle(): string
    {
        return $this->record->name;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [

        ];
    }
}