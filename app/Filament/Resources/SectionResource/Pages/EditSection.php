<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use App\Models\Section;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Section $record) {
                        $record->delete();
                    })
                    ->successRedirectUrl(fn () => static::getResource()::getUrl('index'))
                    ->color('danger')
                    ->label('Delete'),
                Actions\Action::make('restore')
                    ->label('Restore Section')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (Section $record) => $record->trashed())
                    ->action(fn (Section $record) => $record->restore())
                    ->color('success'),
                ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function (Section $record): void {
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
