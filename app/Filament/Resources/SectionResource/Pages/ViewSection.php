<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewSection extends ViewRecord
{
    protected static string $resource = SectionResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Section Information')
                    ->schema([
                        TextEntry::make('office.name')
                            ->label('Office')
                            ->placeholder('No office assigned'),
                        TextEntry::make('head_name')
                            ->label('Head Name')
                            ->placeholder('No head name provided'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
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

                    // ROOT users can edit any section
                    if ($user->role->value === 'ROOT') {
                        return true;
                    }

                    // ADMINISTRATOR users can only edit sections within their office
                    if ($user->role->value === 'ADMINISTRATOR') {
                        return $user->office_id === $this->record->office_id;
                    }

                    return false;
                }),
        ];
    }
}
