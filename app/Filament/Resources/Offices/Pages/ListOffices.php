<?php

namespace App\Filament\Resources\Offices\Pages;

use App\Filament\Resources\Offices\OfficeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Enums\UserRole;

class ListOffices extends ListRecords
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Office'),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        
        if ($user && $user->role !== UserRole::ROOT && $user->office_id) {
            $this->redirect(OfficeResource::getUrl('edit', ['record' => $user->office_id]));
        }
    }
}
