<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use Exception;
use Filament\Forms\Components\TextInput;
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

        $this->modalHeading('Receive document');

        $this->modalDescription('Mark this document as received by your office.');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-inbox-arrow-down');

        $this->form([
            TextInput::make('code')
                ->visible(fn (?Document $record): bool => is_null($record))
                ->rule('required')
                ->markAsRequired()
                ->rule(function () {
                    return function ($attribute, $value, $fail) {
                        $document = Document::firstWhere('code', $value);

                        if (! $document) {
                            $fail('Document with code "' . $value . '" not found.');
                            return;
                        }

                        $transmittal = $document->activeTransmittal;

                        if (! $transmittal) {
                            $fail('This document has no active transmittal. It may not have been transmitted yet.');
                            return;
                        }

                        if ($transmittal->received_at) {
                            $fail('This document has already been received.');
                            return;
                        }

                        if ($transmittal->to_office_id !== Auth::user()->office_id) {
                            $toOfficeName = $transmittal->toOffice->name ?? 'Unknown Office';
                            $userOfficeName = Auth::user()->office->name ?? 'Unknown Office';
                            $fail('This document was sent to "' . $toOfficeName . '" but you are from "' . $userOfficeName . '".');
                            return;
                        }
                    };
                })
                ->validationMessages([
                    'required' => 'Document code is required.',
                    'exists' => 'Document not found.',
                ]),
        ]);

        $this->modalSubmitActionLabel(function (?Document $record): string {
            if (! $record) {
                return 'Receive';
            }

            return $record->electronic ? 'Download' : 'Receive';
        });

        $this->action(function (?Document $record, array $data): void {
            $record = $record ?? Document::firstWhere('code', $data['code']);

            if (!$record) {
                $this->failure();
                return;
            }

            try {
                if ($record->electronic && $record->attachments->isNotEmpty()) {
                    $this->handleElectronicDocumentDownload();
                }

                DB::transaction(function () use ($record) {
                    $activeTransmittal = $record->activeTransmittal;
                    
                    if (!$activeTransmittal) {
                        throw new Exception('No active transmittal found for this document.');
                    }
                    
                    if ($activeTransmittal->to_office_id !== Auth::user()->office_id) {
                        throw new Exception('You are not authorized to receive this document.');
                    }
                    
                    $activeTransmittal->update([
                        'received_at' => now(),
                        'to_user_id' => Auth::id(),
                    ]);
                });

                $this->success();

            } catch (Exception $e) {
                Notification::make()
                    ->title('Failed to receive document')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
                    
                $this->failure();
            }
        });

        $this->successNotificationTitle('Document received successfully');

        $this->failureNotificationTitle('Failed to receive document');
    }

    protected function handleElectronicDocumentDownload(): void
    {
        Notification::make()
            ->title('Document download under development')
            ->body('This feature is not implemented yet as it is currently under development.')
            ->send();
    }
}