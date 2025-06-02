<?php

namespace App\Filament\Actions\Tables;

use App\Models\Document;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnpublishAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('unpublish-document');

        $this->label('Unpublish Document');

        $this->icon('heroicon-o-arrow-uturn-left');

        $this->color('warning');

        $this->modalSubmitActionLabel('Unpublish');

        $this->modalHeading('Unpublish Document');

        $this->modalDescription('This will revert the document back to draft status. The document will become editable again, but QR codes will no longer be available.');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-exclamation-triangle');

        $this->action(function (Document $record): void {
            try {
                DB::transaction(function () use ($record) {

                    if ($record->isDraft()) {
                        throw new \Exception('This document is already in draft status.');
                    }

                    if ($record->user_id !== Auth::id()) {
                        throw new \Exception('You can only unpublish documents you created.');
                    }

                    $record->update([
                        'published_at' => null,
                    ]);

                });

                // Success notification
                Notification::make()
                    ->title('Document Unpublished Successfully')
                    ->body("Document '{$record->title}' has been reverted to draft status and is now editable.")
                    ->warning()
                    ->send();

            } catch (\Exception $e) {
                // Error notification
                Notification::make()
                    ->title('Unpublish Failed')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        // Only show action if document is published
        $this->visible(function (Document $record): bool {
            return $record->isPublished() &&
                   $record->user_id === Auth::id();
        });
    }
}
