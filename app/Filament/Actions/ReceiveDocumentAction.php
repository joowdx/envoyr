<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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
                ->rule('exists:documents,code')
                ->rule(function () {
                    return function ($attribute, $value, $fail) {
                        $document = Document::with('transmittal')->where('code', $value)->first();
                        
                        if (!$document) return; 
                        
                        if (!$document->transmittal) {
                            $fail('No transmittal information found for this document.');
                            return;
                        }
                        
                        if ($document->transmittal->received_at) {
                            $fail('This document has already been received.');
                            return;
                        }
                        
                        if ($document->transmittal->to_office_id !== Auth::user()->office_id) {
                            $fail('You are not authorized to receive this document. It is not addressed to your office.');
                            return;
                        }
                    };
                })
                ->validationMessages([
                    'required' => 'Document code is required.',
                    'exists' => 'The document code does not exist in the system.',
                ])
                ->placeholder('Enter document code to receive'),
        ]);

        $this->action(function (array $data): void {
            $document = Document::with('transmittal')->where('code', $data['code'])->first();

            $document->transmittal->update([
                'received_at' => now(),
            ]);

            Notification::make()
                ->title('Success')
                ->body('Document received successfully.')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->send();
        });
    }
}