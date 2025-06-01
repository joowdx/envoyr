<?php
// filepath: /Users/johnfordleonielbalignot/paperchase/app/Filament/Actions/Tables/ReceiveDocumentAction.php

namespace App\Filament\Actions\Tables;

use App\Models\Document;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiveDocumentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('receive-document');

        $this->label('Receive');

        $this->icon('heroicon-o-inbox-arrow-down');

        $this->color('success');

        $this->modalSubmitActionLabel('Receive Document');

        $this->modalHeading('Receive Document');

        $this->modalDescription('Mark this document as received by your office.');

        $this->form([
            Textarea::make('receive_notes')
                ->label('Receive Notes (Optional)')
                ->placeholder('Add any notes about receiving this document...')
                ->rows(3)
                ->maxLength(500),
        ]);

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-inbox-arrow-down');

        $this->action(function (array $data, Document $record): void {
            try {
                DB::transaction(function () use ($data, $record) {
                    // Find the transmittal for this office
                    $transmittal = $record->transmittals()
                        ->where('office_id', Auth::user()->office_id)
                        ->whereNull('received_at')
                        ->first();

                    if (!$transmittal) {
                        throw new \Exception('No pending transmittal found for this document.');
                    }

                    // Mark as received
                    $transmittal->update([
                        'received_at' => now(),
                        'received_by_id' => Auth::id(),
                    ]);
                    $record->update(['status' => 'received']);
                });

                // Success notification
                Notification::make()
                    ->title('Document Received Successfully')
                    ->body("Document '{$record->title}' has been marked as received by your office.")
                    ->success()
                    ->send();

            } catch (\Exception $e) {
                // Error notification
                Notification::make()
                    ->title('Receive Failed')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        // Only show for documents with pending transmittals to user's office
        $this->visible(function (Document $record): bool {
            return $record->transmittals()
                ->where('office_id', Auth::user()->office_id)
                ->whereNull('received_at')
                ->exists();
        });
    }
}