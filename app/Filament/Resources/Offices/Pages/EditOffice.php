<?php

namespace App\Filament\Resources\Offices\Pages;

use App\Filament\Resources\Offices\OfficeResource;
use Filament\Actions\Action;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->action('save')
                ->keyBindings(['mod+s'])
                ->color('primary'),
            Action::make('cancel')
                ->label('Cancel')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray'),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    
    public function getTitle(): string
    {
        return $this->record->acronym;
    }

    public function getSubheading(): string
    {
        return $this->record->name;
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(array_slice(parent::getBreadcrumbs(), 0, -3),[
           'Office',
           $this->record->acronym,
        ]);
    }
}
