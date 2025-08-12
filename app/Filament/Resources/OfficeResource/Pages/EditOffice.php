<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use App\Models\Office;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Office $record) {
                        $record->delete();
                    })
                    ->successRedirectUrl(fn () => static::getResource()::getUrl('index'))
                    ->color('danger')
                    ->label('Delete'),

                Actions\Action::make('restore')
                    ->label('Restore Office')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (Office $record) => $record->trashed())
                    ->action(fn (Office $record) => $record->restore())
                    ->color('success'),
                ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function (Office $record): void {
                        $record->forceDelete();
                    })
                    ->color('danger')
                    ->label('Permanently Delete'),

            ])
                ->label('Danger Actions')
                ->icon('heroicon-o-ellipsis-vertical'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
