<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\Office;
use App\Models\Section;
use App\Models\Process;
use App\Models\User;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransmitDocument extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = DocumentResource::class;

    protected string $view = 'filament.resources.documents.pages.transmit-document';

    public ?array $data = [];

    public Document $record;

    public function mount(Document $record): void
    {
        $this->record = $record;
        
        if (!$this->canTransmitDocument($record)) {
            Notification::make()
                ->title('Cannot transmit document')
                ->body('You do not have permission to transmit this document.')
                ->danger()
                ->send();
            
            $this->redirect(DocumentResource::getUrl('view', ['record' => $record]));
            return;
        }

        if ($record->activeTransmittal()->exists()) {
            Notification::make()
                ->title('Cannot transmit document')
                ->body('This document has an active transmittal that has not been received yet.')
                ->danger()
                ->send();
            
            $this->redirect(DocumentResource::getUrl('view', ['record' => $record]));
            return;
        }

        $this->loadExistingContents();
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Toggle::make('pick_up')
                    ->label('Pick Up')
                    ->helperText('Enable if the document needs to be picked up')
                    ->default(false)
                    ->live()
                    ->columnSpanFull(),
                Select::make('office_id')
                    ->label('Office')
                    ->options(Office::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! $state) {
                            $set('section_id', null);
                            $set('process_id', null);
                        }
                    }),
                Select::make('section_id')
                    ->label('Section')
                    ->options(function (callable $get) {
                        $officeId = $get('office_id');

                        if (! $officeId) {
                            return [];
                        }

                        $office = Office::find($officeId);

                        if (! $office || $office->id !== Auth::user()->office_id) {
                            return [];
                        }

                        return Section::where('office_id', $officeId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get) => $get('office_id') === Auth::user()->office_id)
                    ->required(fn (callable $get) => $get('office_id') === Auth::user()->office_id),
                Select::make('liaison_id')
                    ->label('Liaison')
                    ->options(function (callable $get) {
                        return User::where('office_id', Auth::user()->office_id)
                            ->when($get('office_id') !== Auth::user()->office_id, function ($query) {
                                return $query->where('role', UserRole::LIAISON);
                            })
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(fn (callable $get) => ! $get('pick_up'))
                    ->visible(fn (callable $get) => ! $get('pick_up')),
                Select::make('process_id')
                    ->label('Process')
                    ->options(function (callable $get) {
                        $toOffice = $get('office_id');
                        if (! $toOffice) {
                            return [];
                        }
                        return Process::where('office_id', $toOffice)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(false)
                    ->columnSpanFull(),
                Textarea::make('remarks')
                    ->label('Remarks')
                    ->nullable()
                    ->maxLength(1000)
                    ->columnSpanFull(),
                    
                Repeater::make('contents')
                    ->addActionLabel('Add Content')
                    ->columnSpanFull()
                    ->orderColumn('sort')
                    ->hint('You can add, remove, or modify content items before transmitting')
                    ->helperText('Each content item represents a file, document, or other attachment')
                    ->itemLabel(fn ($state) => $state['title'] ?? 'New Content')
                    ->collapsed()
                    ->schema([
                        TextInput::make('title')
                            ->label('Content Title')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Enter a descriptive title for this content item'),
                            
                        TextInput::make('context.control')
                            ->label('Control #')
                            ->columnSpan(1)
                            ->helperText('Control number'),
                            
                        TextInput::make('context.pages')
                            ->label('Pages')
                            ->type('number')
                            ->minValue(1)
                            ->columnSpan(1)
                            ->helperText('Number of pages'),
                            
                        TextInput::make('context.copies')
                            ->label('Copies')
                            ->type('number')
                            ->minValue(1)
                            ->columnSpan(1)
                            ->helperText('Number of copies'),
                        
                        TextInput::make('context.particulars')
                            ->label('Particulars')
                            ->columnSpan(1)
                            ->helperText('Additional details'),
                            
                        TextInput::make('context.payee')
                            ->label('Payee')
                            ->columnSpan(1)
                            ->helperText('Payment recipient'),
                            
                        TextInput::make('context.amount')
                            ->label('Amount')
                            ->type('number')
                            ->step('0.01')
                            ->minValue(0)
                            ->columnSpan(1)
                            ->helperText('Monetary amount'),
                        
                        Textarea::make('context.remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Additional notes or remarks'),
                    ])
                    ->columns(3),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('transmit')
                ->label('Transmit Document')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->action('transmit'),
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(DocumentResource::getUrl('view', ['record' => $this->record])),
        ];
    }

    public function transmit(): void
    {
        $data = $this->form->getState();
        
        $this->record->refresh();
        
        if ($this->record->activeTransmittal()->exists()) {
            Notification::make()
                ->title('Cannot transmit document')
                ->body('This document has an active transmittal that has not been received yet.')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::transaction(function () use ($data) {
                if ($this->record->transmittals()->whereNull('received_at')->exists()) {
                    throw new Exception('Document has an active transmittal');
                }
                
                $transmittal = $this->record->transmittals()->create([
                    'process_id' => $data['process_id'] ?? null,
                    'remarks' => $data['remarks'],
                    'from_office_id' => Auth::user()->office_id,
                    'to_office_id' => $data['office_id'],
                    'from_section_id' => Auth::user()->section_id,
                    'to_section_id' => $data['section_id'] ?? null,
                    'from_user_id' => Auth::id(),
                    'liaison_id' => $data['liaison_id'] ?? null,
                    'pick_up' => $data['pick_up'],
                ]);

                $this->createTransmittalAttachmentSnapshot($this->record, $transmittal);

                // No new process row; only link selected process_id to transmittal
            });

            Notification::make()
                ->title('Document transmitted successfully')
                ->success()
                ->send();

            $this->redirect(DocumentResource::getUrl('view', ['record' => $this->record]));

        } catch (Exception $e) {
            Notification::make()
                ->title('Document transmission failed')
                ->body('An error occurred while transmitting the document. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return "Transmit : {$this->record->title}";
    }

    public static function getNavigationLabel(): string
    {
        return 'Transmit Document';
    }

    private function canTransmitDocument(Document $record): bool
    {
        if (!$record->isPublished() || $record->dissemination) {
            return false;
        }

        $currentOfficeId = Auth::user()->office_id;
        
        if ($record->activeTransmittal) {
            return $record->activeTransmittal->to_office_id === $currentOfficeId;
        }
        
        $lastReceivedTransmittal = $record->transmittals()
            ->whereNotNull('received_at')
            ->orderBy('received_at', 'desc')
            ->first();
            
        if ($lastReceivedTransmittal) {
            return $lastReceivedTransmittal->to_office_id === $currentOfficeId;
        }
        
        return $record->office_id === $currentOfficeId;
    }

    private function loadExistingContents(): void
    {
        $attachment = $this->record->attachment;
        if ($attachment) {
            $contents = $attachment->contents()
                ->orderBy('sort')
                ->get()
                ->map(fn ($content) => [
                    'title' => $content->title,
                    'context' => $content->context ?? [],
                ])
                ->toArray();
            
            $this->data['contents'] = $contents;
        } else {
            $this->data['contents'] = [];
        }   
    }

    private function createTransmittalAttachmentSnapshot(Document $document, $transmittal): void
    {
        $data = $this->form->getState();
        
        $transmittalAttachment = $transmittal->attachments()->create([
            'document_id' => $document->id,
        ]);

        if (isset($data['contents']) && is_array($data['contents'])) {
            foreach ($data['contents'] as $index => $contentData) {
                $transmittalAttachment->contents()->create([
                    'sort' => $index + 1,
                    'title' => $contentData['title'] ?? 'Untitled Content',
                    'context' => $contentData['context'] ?? [],
                ]);
            }
        } else {
            $draftAttachment = $document->attachment;
            if ($draftAttachment) {
                foreach ($draftAttachment->contents as $content) {
                    $transmittalAttachment->contents()->create([
                        'sort' => $content->sort,
                        'title' => $content->title,
                        'file' => $content->file,
                        'path' => $content->path,
                        'hash' => $content->hash,
                        'context' => $content->context,
                    ]);
                }
            }
        }
    }
}
