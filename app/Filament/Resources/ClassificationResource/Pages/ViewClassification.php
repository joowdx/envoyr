<?php

namespace App\Filament\Resources\ClassificationResource\Pages;

use App\Filament\Resources\ClassificationResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewClassification extends ViewRecord
{
    protected static string $resource = ClassificationResource::class;

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
            Actions\EditAction::make()
                ->slideOver()
                ->modalWidth('md'),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    if ($this->record->documents()->exists()) {
                        Notification::make()
                            ->title('Cannot Delete Classification')
                            ->body('This classification cannot be deleted because it has documents associated with it. Please remove all documents first.')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Classification deleted')
                        ->body('The classification has been deleted successfully.')
                )
                ->requiresConfirmation()
                ->modalHeading('Delete Classification')
                ->modalDescription('Are you sure you want to delete this classification? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete it'),
        ];
    }
}
