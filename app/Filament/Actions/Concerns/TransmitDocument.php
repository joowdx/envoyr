<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\UserRole;
use App\Models\Document;
use App\Models\Office;
use App\Models\Section;
use App\Models\Process;
use App\Models\Transmittal;
use App\Models\User;
use Exception;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait TransmitDocument
{
    protected function bootTransmitDocument(): void
    {
        $this->name('transmit-document');

        $this->label('Transmit');

        $this->icon('heroicon-o-paper-airplane');

        $this->modalSubmitActionLabel('Transmit');

        $this->modalHeading('Transmit document');

        $this->modalDescription('Transmit this document to another office or section.');

        $this->modalIcon('heroicon-o-paper-airplane');

        $this->slideOver();

        $this->modalWidth('lg');

        $this->form([
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
                    }
                    $set('process_id', null);
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
                ->options(function (Get $get) {
                    $toOffice = $get('office_id');
                    if (! $toOffice) {
                        return [];
                    }
                    return Process::where('office_id', $toOffice)
                        ->orderBy('status')
                        ->pluck('status', 'id');
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
        ]);

        $this->action(function (Document $record, array $data) {
            $record->refresh();
            
            if ($record->activeTransmittal()->exists()) {
                $this->failureNotificationTitle('Cannot transmit document');
                $this->failureNotificationBody('This document has an active transmittal that has not been received yet.');
                $this->failure();
                return;
            }
            try {
                DB::transaction(function () use ($record, $data) {
                    if ($record->transmittals()->whereNull('received_at')->exists()) {
                        throw new Exception('Document has an active transmittal');
                    }
                    
                    $transmittal = $record->transmittals()->create([
                        'process_id' => $data['process_id'] ?? null,
                        'remarks' => $data['remarks'] ?? null,
                        'from_office_id' => Auth::user()->office_id,
                        'to_office_id' => $data['office_id'],
                        'from_section_id' => Auth::user()->section_id,
                        'to_section_id' => $data['section_id'] ?? null,
                        'from_user_id' => Auth::id(),
                        'liaison_id' => $data['liaison_id'] ?? null,
                        'pick_up' => $data['pick_up'],
                    ]);

                    $this->createTransmittalAttachmentSnapshot($record, $transmittal);
                });

                $this->success();
            } catch (Exception $e) {
                throw $e;

                $this->failure();
            }
        });

        $this->visible(
            fn (Document $record): bool => $record->isPublished() &&
                $this->canTransmitDocument($record) &&
                ! $record->activeTransmittal()->exists() &&
                ! $record->dissemination
        );

        $this->successNotificationTitle('Document transmitted successfully');

        $this->failureNotificationTitle('Document transmission failed');
    }

    private function canTransmitDocument(Document $record): bool
    {
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

    private function createTransmittalAttachmentSnapshot(Document $document, Transmittal $transmittal): void
    {
        $draftAttachment = $document->attachment;

        if ($draftAttachment) {
            $transmittalAttachment = $transmittal->attachments()->create([
                'document_id' => $document->id,
            ]);

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