<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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
        $this->modalSubmitActionLabel('Receive');

        $this->form([
            TextInput::make('code')
                ->label('Document Code')
                ->required()
                ->rule(function () {
                    return function ($attribute, $value, $fail) {
                        $document = Document::where('code', $value)->first();

                        if (! $document) {
                            $fail('Document with this code does not exist.');

                            return;
                        }

                        // Use activeTransmittal to get unreceived transmittal
                        $transmittal = $document->activeTransmittal;
                        if (! $transmittal) {
                            $fail('No active transmittal found for this document or it has already been received.');

                            return;
                        }

                        if ($transmittal->to_office_id !== Auth::user()->office_id) {
                            $fail('You are not authorized to receive this document. It is not addressed to your office.');

                            return;
                        }
                    };
                })
                ->validationMessages([
                    'required' => 'Document code is required.',
                ])
                ->placeholder('Enter document code to receive'),
        ]);

        $this->action(function (array $data): void {
            try {
                DB::transaction(function () use ($data) {
                    $document = Document::where('code', $data['code'])
                        ->lockForUpdate()
                        ->first();

                    if (! $document) {
                        throw new \Exception('Document not found.');
                    }

                    $transmittal = $document->activeTransmittal;
                    if (! $transmittal) {
                        throw new \Exception('No active transmittal found or document already received.');
                    }

                    if ($transmittal->to_office_id !== Auth::user()->office_id) {
                        throw new \Exception('You are not authorized to receive this document.');
                    }

                    if ($transmittal->received_at) {
                        throw new \Exception('This document has already been received.');
                    }

                    $transmittal->update([
                        'received_at' => now(),
                        'received_by_id' => Auth::id(),
                    ]);
                });

                Notification::make()
                    ->title('Success')
                    ->body('Document received successfully.')
                    ->success()
                    ->icon('heroicon-o-check-circle')
                    ->send();

                $this->redirect(request()->header('Referer'));

            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error')
                    ->body($e->getMessage())
                    ->danger()
                    ->icon('heroicon-o-exclamation-triangle')
                    ->send();
            }
        });
    }
}
