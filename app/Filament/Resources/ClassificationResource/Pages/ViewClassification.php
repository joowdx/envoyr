<?php

namespace App\Filament\Resources\ClassificationResource\Pages;

use App\Filament\Resources\ClassificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewClassification extends ViewRecord
{
    protected static string $resource = ClassificationResource::class;

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
