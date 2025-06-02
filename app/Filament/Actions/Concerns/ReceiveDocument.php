<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-inbox-arrow-down');

        $this->modalSubmitActionLabel(function (?Document $record): string {
            if (! $record) {
                return 'Receive Document';
            }

            return $record->electronic ? 'Download & Receive' : 'Receive Document';
        });

        $this->action(function (Document $record): void {
            try {
                DB::transaction(function () use ($record) {
                    $transmittal = $record->activeTransmittal;

                    if (is_null($transmittal)) {
                        throw new Exception('No pending transmittal found for this document.');
                    }

                    if ($transmittal->to_office_id !== Auth::user()->office_id) {
                        throw new Exception('This document is not intended for your office.');
                    }

                    $transmittal->update([
                        'received_at' => now(),
                        'to_user_id' => Auth::id(),
                    ]);
                });

                $this->sendCustomSuccessNotification($record);

                if ($record->electronic && $record->attachment && $record->attachment->files->isNotEmpty()) {
                    $this->handleElectronicDocumentDownload($record);
                }

            } catch (Exception $e) {
                $this->sendCustomFailureNotification($e->getMessage());
            }
        });
    }

    protected function handleElectronicDocumentDownload(Document $record): void
    {
        $attachment = $record->attachment;
        if ($attachment && $attachment->files->isNotEmpty()) {
            $fileName = $attachment->paths->first();

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

        if (! $record->electronic) {
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
