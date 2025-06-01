<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublishAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('publish-document');

        $this->label('Publish Document');

        $this->icon('heroicon-o-paper-airplane');

        $this->color('success');

        $this->modalSubmitActionLabel('Publish');

        $this->modalHeading('Publish Document');

        $this->modalDescription('Once published, this document will be finalized and cannot be edited. You will be able to generate QR codes for tracking.');

        $this->form([
            Textarea::make('publish_notes')
                ->label('Publication Notes')
                ->placeholder('Add any final notes or comments about this publication...')
                ->rows(3)
                ->maxLength(500),
        ]);

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-check-circle');

        $this->action(function (array $data, Document $record): void {
            try {
                DB::transaction(function () use ($data, $record) {
                    // Check if document is already published
                    if ($record->published_at) {
                        throw new \Exception('This document has already been published.');
                    }

                    // Update document with publication data
                    $record->update([
                        'published_at' => now(),
                        'unpublished_at' => null,  
                        'status' => 'published',
                    ]);
                });

                // Success notification
                Notification::make()
                    ->title('Document Published Successfully')
                    ->body("Document '{$record->title}' has been published and is now finalized. You can now generate QR codes for tracking.")
                    ->success()
                    ->send();

            } catch (\Exception $e) {
                // Error notification
                Notification::make()
                    ->title('Publication Failed')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        // Only show action if document is not published yet
        $this->visible(function (Document $record): bool {
            return is_null($record->published_at) && 
                   $record->user_id === Auth::id();
        });
    }
}
