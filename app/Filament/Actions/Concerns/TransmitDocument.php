<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use App\Models\Office;
use App\Models\Section;
use App\Models\User;
use Exception;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

        $this->color('success');

        $this->modalSubmitActionLabel('Transmit');

        $this->modalHeading('Transmit Document');

        $this->modalDescription('Transmit this document to another office or section.');

        $this->modalIcon('heroicon-o-paper-airplane');

        $this->slideOver();

        $this->modalWidth('lg');

        $this->form([
            Select::make('office_id')
                ->label('Office')
                ->options(Office::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!$state) {
                        $set('section_id', null);
                    }
                }),
            Select::make('section_id')
                ->label('Section')
                ->options(function (Get $get) {
                    $officeId = $get('office_id');

                    if (!$officeId) {
                        return [];
                    }

                    $office = Office::find($officeId);

                    if (!$office || $office->id !== Auth::user()->office_id) {
                        return [];
                    }

                    return Section::where('office_id', $officeId)
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->visible(fn(Get $get) => $get('office_id') === Auth::user()->office_id),
            Select::make('liaison_id')
                ->label('Liaison')
                ->options(function (callable $get) {
                    return User::where('office_id', Auth::user()->office_id)
                        ->when($get('office_id') !== Auth::user()->office_id, function ($query) {
                            return $query->where('role', 'liaison');
                        })
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->required(),
            Textarea::make('purpose')
                ->label('Purpose')
                ->markAsRequired()
                ->rule('required')
                ->maxLength(1000)
                ->columnSpanFull(),
            Textarea::make('remarks')
                ->label('Remarks')
                ->nullable()
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);

        $this->action(function (Document $record, array $data) {
            try {
                DB::transaction(function () use ($record, $data) {
                    $record->transmittals()->create([
                        'purpose' => $data['purpose'],
                        'remarks' => $data['remarks'],
                        'from_office_id' => Auth::user()->office_id,
                        'to_office_id' => $data['office_id'],
                        'from_section_id' => Auth::user()->section_id,
                        'to_section_id' => $data['section_id'],
                        'from_user_id' => Auth::id(),
                        'liaison_id' => $data['liaison_id'],
                    ]);
                });

                $this->success();
            } catch (Exception $e) {
                $this->failure();
            }
        });

        $this->visible(fn (Document $record): bool =>
            $record->isPublished() &&
            $record->user_id === Auth::id() &&
            !$record->transmitted_at &&
            !$record->dissemination
        );

        $this->successNotificationTitle('Document transmitted successfully');

        $this->failureNotificationTitle('Document transmission failed');
    }
}
