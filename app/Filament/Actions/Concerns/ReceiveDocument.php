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
                            $fail('Document not found.');

                            return;
                        }

                        $transmittal = $document->activeTransmittal;

                        if (! $transmittal || $transmittal->to_office_id !== Auth::user()->office_id) {
                            $fail('You are not authorized to receive this document.');
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
            $record = $record ?? Document::where('code', $data['code'])->first();

            try {
                if ($record->electronic && $record->attachments->isNotEmpty()) {
                    $this->handleElectronicDocumentDownload();
                }

                DB::transaction(function () use ($record) {
                    $record->activeTransmittal->update([
                        'received_at' => now(),
                        'to_user_id' => Auth::id(),
                    ]);
                });

                $this->success();

            } catch (Exception) {
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