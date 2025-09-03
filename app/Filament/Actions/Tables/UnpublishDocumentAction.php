<?php

namespace App\Filament\Actions\Tables;

use Exception;
use App\Models\Document;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class UnpublishDocumentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('unpublish-document');

        $this->label('Unpublish');

        $this->icon('heroicon-o-arrow-uturn-left');

        $this->requiresConfirmation();

        $this->modalSubmitActionLabel('Unpublish');

        $this->modalHeading('Unpublish document');

        $this->modalDescription('This will revert the document back to draft status.');

        $this->modalIcon('heroicon-o-exclamation-triangle');

        $this->action(function (Document $record): void {
            try {
                DB::transaction(function () use ($record) {
                    if ($record->isDraft()) {
                        throw new Exception('This document is already in draft status.');
                    }

                    if ($record->transmittal) {
                        throw new Exception('This document can no longer be unpublished.');
                    }

                    if ($record->user_id !== Auth::id()) {
                        throw new Exception('You can only unpublish documents you created.');
                    }

                    $record->update([
                        'published_at' => null,
                    ]);
                });

                Notification::make()
                    ->title('Document unpublished successfully')
                    ->body("Document '{$record->title}' has been reverted to draft status and is now editable.")
                    ->warning()
                    ->send();
            } catch (Exception) {
                $this->failure();
            }
        });

        $this->visible(function (Document $record): bool {
            return $record->isPublished() &&
                $record->transmittal === null &&
                $record->user_id === Auth::id();
        });

        $this->failureNotificationTitle('Unpublish failed');
    }
}