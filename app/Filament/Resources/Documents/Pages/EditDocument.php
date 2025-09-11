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
                ])
                ->toArray();
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        
        // Get or create the main attachment
        $attachment = $this->record->attachment;
        if (!$attachment) {
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
                    'sort' => $index + 1,
                ]);
            }
        }
    }
}
