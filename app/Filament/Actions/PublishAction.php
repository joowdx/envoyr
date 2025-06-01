<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublishAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('publish');

        $this->label('Publish');

        $this->icon('heroicon-o-eye');

        $this->color('success');

        $this->modalSubmitActionLabel('Publish Document');

        $this->modalHeading('Publish Document');

        $this->modalDescription('Are you sure you want to publish this document? Once published, it cannot be edited.');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-eye');

        $this->action(function (array $data, Document $record): void {
            try {
                DB::transaction(function () use ($record) {
                    // Check if document is already published
                    if ($record->isPublished()) {
                        throw new \Exception('This document is already published.');
                    }

                    // Check if user has permission to publish
                    if ($record->user_id !== Auth::id()) {
                        throw new \Exception('You can only publish documents you created.');
                    }

                    // Update document to published status
                    $record->update([
                        'published_at' => now(),
                    ]);

                });

                // Success notification
                Notification::make()
                    ->title('Document Published Successfully')
                    ->body("Document '{$record->title}' is now published.")
                    ->success()
                    ->send();

            } catch (\Exception $e) {
                // Error notification
                Notification::make()
                    ->title('Publish Failed')
                    ->danger()
                    ->send();
            }
        });

        // Only show for draft documents
        $this->visible(function (Document $record): bool {
            return $record->isDraft();
        });
    }
}
