<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOffice extends ViewRecord
{
    protected static string $resource = OfficeResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Office Information')
                    ->schema([
                        TextEntry::make('acronym')
                            ->label('Acronym')
                            ->placeholder('No acronym provided'),
                        TextEntry::make('head_name')
                            ->label('Head Name')
                            ->placeholder('No head name provided'),
                    ])
                    ->columns(2),

                Section::make('Administrative Details')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(function () {
                    $user = Auth::user();

                    // ROOT users can edit any office
                    if ($user->role->value === 'ROOT') {
                        return true;
                    }

                    // ADMINISTRATOR users can only edit their own office
                    if ($user->role->value === 'ADMINISTRATOR') {
                        return $user->office_id === $this->record->id;
                    }

                    return false;
                }),
        ];
    }
}
