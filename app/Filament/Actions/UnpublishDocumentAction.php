<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class UnpublishDocumentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('unpublish-document');

        $this->label('Unpublish');

        $this->icon('heroicon-o-arrow-uturn-down');

        $this->requiresConfirmation();

        $this->modalHeading('Unpublish Document');

        $this->modalDescription('Are you sure you want to unpublish this document? This will revert it to draft status and remove it from public access. Note: Documents that have been transmitted cannot be unpublished.');

        $this->modalIcon('heroicon-o-arrow-uturn-down');

        $this->visible(fn (Document $record): bool => $record->isPublished() && ! $record->hasTransmittals()
        );

        $this->action(function (Document $record): void {
            try {
                DB::transaction(function () use ($record) {
                    // Double-check the condition before unpublishing
                    if ($record->hasTransmittals()) {
                        throw new Exception('Cannot unpublish document that has already been transmitted.');
                    }

                    $record->update([
                        'status' => 'draft',
                        'published_at' => null,
                    ]);
                });

                Notification::make()
                    ->title('Document Unpublished')
                    ->body('The document has been successfully unpublished and reverted to draft status.')
                    ->success()
                    ->send();

            } catch (Exception $e) {
                Notification::make()
                    ->title('Unpublish Failed')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

    }
}
