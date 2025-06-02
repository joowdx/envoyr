<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use Exception;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ReceiveDocument
{
    protected function bootReceiveDocument(): void
    {
        $this->name('receive-document');

        $this->label('Receive');

        $this->icon('heroicon-o-inbox-arrow-down');

        $this->color('success');

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

        // Set the modal submit action label based on whether document is electronic
        $this->modalSubmitActionLabel(function (?Document $record): string {
            if (!$record) {
                return 'Receive Document';
            }
            return $record->electronic ? 'Download & Receive' : 'Receive Document';
        });

        $this->action(function (array $data, Document $record): void {
            try {
                DB::transaction(function () use ($data, $record) {
                    // Find the transmittal for this office
                    $transmittal = $record->transmittals()
                        ->where('to_office_id', Auth::user()->office_id)
                        ->whereNull('received_at')
                        ->first();

                    if (!$transmittal) {
                        throw new Exception('No pending transmittal found for this document.');
                    }

                    // Mark as received
                    $transmittal->update([
                        'received_at' => now(),
                        'received_by_id' => Auth::id(),
                    ]);
                });

                // Show success notification
                $this->sendCustomSuccessNotification($record);

                // For electronic documents, trigger download after successful receive
                if ($record->electronic && $record->attachment && $record->attachment->files->isNotEmpty()) {
                    $this->handleElectronicDocumentDownload($record);
                }

            } catch (Exception $e) {
                $this->sendCustomFailureNotification($e->getMessage());
            }
        });

        // Only show for documents with pending transmittals to user's office
        $this->visible(function (?Document $record): bool {
            if (!$record) {
                return false;
            }
            return $record->transmittals()
                ->where('to_office_id', Auth::user()->office_id)
                ->whereNull('received_at')
                ->exists();
        });
    }

    protected function handleElectronicDocumentDownload(Document $record): void
    {
        $attachment = $record->attachment;
        if ($attachment && $attachment->files->isNotEmpty()) {
            $fileName = $attachment->paths->first();
            
            // Create a success notification with download link
            Notification::make()
                ->title('Document Received & Ready for Download')
                ->body("Electronic document '{$fileName}' is ready for download.")
                ->success()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('download')
                        ->label('Download File')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(route('document.download', $record->id))
                        ->openUrlInNewTab(),
                ])
                ->persistent()
                ->send();
        }
    }

    protected function sendCustomSuccessNotification(Document $record): void
    {
        // Only send this notification for non-electronic documents
        // Electronic documents get their own notification with download link
        if (!$record->electronic) {
            Notification::make()
                ->title($this->getSuccessNotificationTitle())
                ->body("Document '{$record->title}' has been marked as received by your office.")
                ->success()
                ->send();
        }
    }

    protected function sendCustomFailureNotification(string $message): void
    {
        Notification::make()
            ->title($this->getFailureNotificationTitle())
            ->body($message)
            ->danger()
            ->send();
    }

    public function getSuccessNotificationTitle(): string
    {
        return 'Document Received Successfully';
    }

    public function getFailureNotificationTitle(): string
    {
        return 'Receive Failed';
    }
}
