<?php

namespace App\Filament\Actions\Concerns;

use Filament\Infolists;
use App\Models\Document;
use App\Models\Transmittal;
use Infolists\Components\Tabs\Tab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

trait TransmittalHistoryInfolist
{
    protected static function getTransmittalHistorySchema(): array
    {
        return [
            Tabs::make('contents')
                ->contained(false)
                ->tabs([
                    Tabs::make('Current State')
                        ->visible(fn ($record) => $record->transmittal !== null)
                        ->schema([
                            Group::make()
                                ->relationship('transmittal')
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            TextEntry::make('code')
                                                ->extraAttributes(['class' => 'font-mono'])
                                                ->copyable()
                                                ->copyMessage('Copied!')
                                                ->copyMessageDuration(1500),
                                            TextEntry::make('liaison.name'),
                                            TextEntry::make('toOffice.name')
                                                ->label('To')
                                                ->helperText(fn ($record) => $record->section->name),
                                            TextEntry::make('fromOffice.name')
                                                ->label('From')
                                                ->helperText(fn ($record) => $record->section->name),
                                            TextEntry::make('created_at')
                                                ->label('Transmitted')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->helperText(fn ($record) => $record->transmittal?->fromUser?->name ?? 'Unknown'),
                                            TextEntry::make('received_at')
                                                ->label(fn (Document $record) => $record->pick_up ? 'Picked up at' : 'Received At')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->placeholder('Not yet received')
                                                ->helperText(function (Document $record) {
                                                    if (! $record->transmittal?->received_at) {
                                                        return 'Not yet received';
                                                    }

                                                    return 'By '.($record->transmittal?->toUser?->name ?? 'Unknown');
                                                }),
                                            TextEntry::make('purpose')
                                                ->label('Purpose')
                                                ->columnSpanFull(),
                                        ]),
                                    static::attachmentInfolistGroup(),
                                ]),
                        ]),
                    Tabs::make('Transmittal Transactions')
                        ->schema([
                            RepeatableEntry::make('transmittals')
                                ->hiddenLabel()
                                // ->contained(false)
                                ->schema([
                                    // Tabs::make()
                                    //     ->tabs([
                                    //         Tabs\Tab::make('Overview')
                                    //             ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('code')
                                                ->extraAttributes(['class' => 'font-mono'])
                                                ->copyable()
                                                ->copyMessage('Copied!')
                                                ->copyMessageDuration(1500),
                                            TextEntry::make('liaison.name'),
                                            TextEntry::make('toOffice.name')
                                                ->label('To')
                                                ->helperText(fn ($record) => $record->toSection?->name),
                                            TextEntry::make('fromOffice.name')
                                                ->label('From')
                                                ->helperText(fn ($record) => $record->fromSection?->name),
                                            TextEntry::make('created_at')
                                                ->label('Transmitted')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->helperText(fn ($record) => 'By '.($record->fromUser?->name ?? 'Unknown')),
                                            TextEntry::make('received_at')
                                                ->label(fn (Transmittal $record) => $record->pick_up ? 'Picked up' : 'Received')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->placeholder('Not yet received')
                                                ->helperText(function (Transmittal $record) {
                                                    if (! $record->received_at) {
                                                        return 'Not yet received';
                                                    }

                                                    return 'By '.($record?->toUser?->name ?? 'Unknown');
                                                }),
                                        ]),
                                    TextEntry::make('purpose')
                                        ->label('Purpose')
                                        ->columnSpanFull(),
                                    // ]),
                                    // Tabs\Tab::make('Remarks')
                                    //     ->hidden(fn ($record) => $record->remarks === null)
                                    //     ->schema([
                                    TextEntry::make('remarks')
                                        ->markdown()
                                        ->columnSpanFull()
                                        ->visible(fn ($record) => $record->remarks !== null),
                                    // ]),
                                    Tabs::make('Contents')
                                        ->schema([static::attachmentInfolistGroup()]),
                                ]),
                            // ]),

                        ]),
                    Tabs::make('Original Contents')
                        ->schema([static::attachmentInfolistGroup()]),
                ]),
        ];
    }
}
