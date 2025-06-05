<?php

namespace App\Filament\Resources\SourceResource\Pages;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\SourceResource;
use Filament\Infolists\Components\TextEntry;





class ViewSources extends ViewRecord
{
    protected static string $resource = SourceResource::class;
    public function getTitle(): string
    {
        return $this->record->name;
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('description')
                    ->hiddenLabel(true)
                    ->placeholder('No description provided'),
            ]);
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