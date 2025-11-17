<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Attachment;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;
        $data['office_id'] = auth()->user()->office_id;
        $data['section_id'] = auth()->user()->section_id;

        // Remove contents from document data since it's handled separately
        unset($data['contents']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        // Create the main attachment for this document
        $attachment = Attachment::create([
            'document_id' => $this->record->id,
            'transmittal_id' => null, // Main document attachment
        ]);

        // Create contents if provided
        if (isset($data['contents']) && is_array($data['contents'])) {
            foreach ($data['contents'] as $index => $contentData) {
                $attachment->contents()->create([
                    'title' => $contentData['title'],
                    'copies' => $contentData['copies'] ?? 1,
                    'pages_per_copy' => $contentData['pages_per_copy'] ?? 1,
                    'control_number' => $contentData['control_number'] ?? null,
                    'particulars' => $contentData['particulars'] ?? null,
                    'payee' => $contentData['payee'] ?? null,
                    'amount' => $contentData['amount'] ?? null,
                    'file' => $contentData['file'] ?? null,
                    'context' => $contentData['context'] ?? null,
                    'sort' => $index + 1,
                ]);
            }
        }
    }
}
