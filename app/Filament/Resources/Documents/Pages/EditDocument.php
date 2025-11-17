<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Attachment;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->isDraft()),
            ForceDeleteAction::make()
                ->visible(fn (): bool => $this->record->isDraft()),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load contents from the main attachment
        $attachment = $this->record->attachment;
        if ($attachment) {
            $data['contents'] = $attachment->contents()
                ->orderBy('sort')
                ->get()
                ->map(fn ($content) => [
                    'title' => $content->title,
                    'copies' => $content->copies,
                    'pages_per_copy' => $content->pages_per_copy,
                    'control_number' => $content->control_number,
                    'particulars' => $content->particulars,
                    'payee' => $content->payee,
                    'amount' => $content->amount,
                    'file' => $content->file,
                    'context' => $content->context,
                ])
                ->toArray();
        } else {
            // If no attachment exists, provide default empty content
            $data['contents'] = [
                [
                    'title' => '',
                    'copies' => 1,
                    'pages_per_copy' => 1,
                    'control_number' => '',
                    'particulars' => '',
                    'payee' => '',
                    'amount' => null,
                    'file' => null,
                    'context' => '',
                ],
            ];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        // Get or create the main attachment
        $attachment = $this->record->attachment;
        if (! $attachment) {
            $attachment = Attachment::create([
                'document_id' => $this->record->id,
                'transmittal_id' => null,
            ]);
        }

        // Clear existing contents and recreate them
        $attachment->contents()->delete();

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove contents from document data since it's handled separately
        unset($data['contents']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
