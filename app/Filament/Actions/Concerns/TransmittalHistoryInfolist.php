<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use App\Models\Transmittal;
use Filament\Infolists;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Tables\Columns\Layout\Grid;
use Infolists\Components\Tabs\Tab;

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
                                            Infolists\Components\TextEntry::make('code')
                                                ->extraAttributes(['class' => 'font-mono'])
                                                ->copyable()
                                                ->copyMessage('Copied!')
                                                ->copyMessageDuration(1500),
                                            Infolists\Components\TextEntry::make('liaison.name'),
                                            Infolists\Components\TextEntry::make('toOffice.name')
                                                ->label('To')
                                                ->helperText(fn ($record) => $record->section->name),
                                            Infolists\Components\TextEntry::make('fromOffice.name')
                                                ->label('From')
                                                ->helperText(fn ($record) => $record->section->name),
                                            Infolists\Components\TextEntry::make('created_at')
                                                ->label('Transmitted')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->helperText(fn ($record) => $record->transmittal?->fromUser?->name ?? 'Unknown'),
                                            Infolists\Components\TextEntry::make('received_at')
                                                ->label(fn (Document $record) => $record->pick_up ? 'Picked up at' : 'Received At')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->placeholder('Not yet received')
                                                ->helperText(function (Document $record) {
                                                    if (! $record->transmittal?->received_at) {
                                                        return 'Not yet received';
                                                    }

                                                    return 'By '.($record->transmittal?->toUser?->name ?? 'Unknown');
                                                }),
                                            Infolists\Components\TextEntry::make('purpose')
                                                ->label('Purpose')
                                                ->columnSpanFull(),
                                        ]),
                                    static::attachmentInfolistGroup(),
                                ]),
                        ]),
                    Tabs::make('Transmittal Transactions')
                        ->schema([
                            Infolists\Components\RepeatableEntry::make('transmittals')
                                ->hiddenLabel()
                                // ->contained(false)
                                ->schema([
                                    // Infolists\Components\Tabs::make()
                                    //     ->tabs([
                                    //         Infolists\Components\Tabs\Tab::make('Overview')
                                    //             ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('code')
                                                ->extraAttributes(['class' => 'font-mono'])
                                                ->copyable()
                                                ->copyMessage('Copied!')
                                                ->copyMessageDuration(1500),
                                            Infolists\Components\TextEntry::make('liaison.name'),
                                            Infolists\Components\TextEntry::make('toOffice.name')
                                                ->label('To')
                                                ->helperText(fn ($record) => $record->toSection?->name),
                                            Infolists\Components\TextEntry::make('fromOffice.name')
                                                ->label('From')
                                                ->helperText(fn ($record) => $record->fromSection?->name),
                                            Infolists\Components\TextEntry::make('created_at')
                                                ->label('Transmitted')
                                                ->dateTime('jS F Y \a\t H:i')
                                                ->helperText(fn ($record) => 'By '.($record->fromUser?->name ?? 'Unknown')),
                                            Infolists\Components\TextEntry::make('received_at')
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
                                    Infolists\Components\TextEntry::make('purpose')
                                        ->label('Purpose')
                                        ->columnSpanFull(),
                                    // ]),
                                    // Infolists\Components\Tabs\Tab::make('Remarks')
                                    //     ->hidden(fn ($record) => $record->remarks === null)
                                    //     ->schema([
                                    Infolists\Components\TextEntry::make('remarks')
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
