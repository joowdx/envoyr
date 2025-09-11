<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModifyAttachmentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'modify-attachments';
    }

    protected function setUp(): void
    {
        $this->name('modify-attachments');

        $this->label('Modify Attachments');

        $this->icon('heroicon-o-paper-clip');

        $this->modalHeading('Modify Document Attachments');

        $this->modalDescription('Add, remove, or modify attachments for this received document before retransmission.');

        $this->modalWidth('lg');

        $this->form([
            Forms\Components\Repeater::make('current_contents')
                ->label('Current Files (received with document)')
                ->helperText('Review and modify the attachments received with this document.')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('File Title')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TagsInput::make('file')
                        ->label('Files')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Toggle::make('keep')
                        ->label('Keep this attachment')
                        ->default(true)
                        ->live(),
                ])
                ->defaultItems(function (Document $record) {
                    $activeTransmittal = $record->activeTransmittal;
                    if (!$activeTransmittal || !$activeTransmittal->attachments->first()) {
                        return [];
                    }
                    
                    return $activeTransmittal->attachments->first()->contents->map(function ($content) {
                        return [
                            'id' => $content->id,
                            'title' => $content->title,
                            'file' => $content->file ? $content->file->toArray() : [],
                            'keep' => true,
                        ];
                    })->toArray();
                })
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Untitled')
                ->collapsible()
                ->reorderable(false)
                ->addable(false)
                ->deletable(false),

            Forms\Components\Repeater::make('new_contents')
                ->label('Add New Files')
                ->helperText('Add additional attachments to this document.')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->label('File Title'),
                    Forms\Components\FileUpload::make('file')
                        ->multiple()
                        ->label('Upload Files')
                        ->preserveFilenames()
                        ->directory('attachments')
                        ->visibility('private')
                        ->required(),
                    Forms\Components\Textarea::make('context')
                        ->label('Notes/Context')
                        ->rows(2),
                ])
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Attachment')
                ->addActionLabel('Add New Attachment')
                ->collapsible(),
        ]);

        $this->action(function (Document $record, array $data) {
            try {
                DB::transaction(function () use ($record, $data) {
                    $this->updateDocumentAttachments($record, $data);
                });

                Notification::make()
                    ->title('Attachments modified successfully')
                    ->success()
                    ->send();

            } catch (\Exception $e) {
                Notification::make()
                    ->title('Failed to modify attachments')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        $this->visible(function (Document $record): bool {
            $activeTransmittal = $record->activeTransmittal;
            
            return $activeTransmittal && 
                   $activeTransmittal->received_at && 
                   $activeTransmittal->to_office_id === Auth::user()->office_id;
        });

        $this->modalSubmitActionLabel('Save Changes');
    }

    private function updateDocumentAttachments(Document $document, array $data): void
    {
        // Get the current draft attachment or create one
        $draftAttachment = $document->attachment;
        
        if (!$draftAttachment) {
            $draftAttachment = $document->attachments()->create([
                'transmittal_id' => null, // Draft attachment
            ]);
        }

        // Clear existing contents
        $draftAttachment->contents()->delete();

        // Add kept contents from current transmittal
        $currentContents = $data['current_contents'] ?? [];
        foreach ($currentContents as $contentData) {
            if ($contentData['keep'] ?? false) {
                // Find the original content and copy it
                $originalContent = \App\Models\Content::find($contentData['id']);
                if ($originalContent) {
                    $draftAttachment->contents()->create([
                        'title' => $originalContent->title,
                        'file' => $originalContent->file,
                        'path' => $originalContent->path,
                        'hash' => $originalContent->hash,
                        'context' => $originalContent->context,
                        'sort' => $originalContent->sort,
                    ]);
                }
            }
        }

        // Add new contents
        $newContents = $data['new_contents'] ?? [];
        foreach ($newContents as $index => $contentData) {
            $draftAttachment->contents()->create([
                'title' => $contentData['title'],
                'file' => $contentData['file'] ?? [],
                'context' => $contentData['context'] ?? null,
                'sort' => count($currentContents) + $index,
            ]);
        }
    }
}
