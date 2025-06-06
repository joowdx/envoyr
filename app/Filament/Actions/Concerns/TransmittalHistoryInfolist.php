<?php

namespace App\Filament\Actions\Concerns;

use Filament\Infolists;

trait TransmittalHistoryInfolist
{
    protected static function getTransmittalHistorySchema(): array
    {
        return [
            Infolists\Components\RepeatableEntry::make('transmittals')
                ->contained(false)
                ->schema([
                    Infolists\Components\Tabs::make('Details')
                        ->tabs([
                            Infolists\Components\Tabs\Tab::make('Overview')
                                ->schema([
                                    Infolists\Components\TextEntry::make('code')
                                        ->label('Code')
                                        ->extraAttributes(['class' => 'font-mono'])
                                        ->copyable()
                                        ->copyMessage('Copied!')
                                        ->copyMessageDuration(1500),
                                    Infolists\Components\Grid::make(3)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('fromOffice.name')
                                                ->label('From'),
                                            Infolists\Components\TextEntry::make('toOffice.name')
                                                ->label('To'),
                                            Infolists\Components\TextEntry::make('fromSection.name')
                                                ->label('From Section')
                                                ->visible(fn ($record) => $record->fromSection !== null),
                                            Infolists\Components\TextEntry::make('toSection.name')
                                                ->label('To Section')
                                                ->visible(fn ($record) => $record->toSection !== null),
                                            Infolists\Components\TextEntry::make('fromUser.name')
                                                ->label('Transmitted')
                                                ->helperText(fn ($record) => $record->created_at?->format('Y-m-d H:i:s')),
                                            Infolists\Components\TextEntry::make('liaison.name')
                                                ->label('Liaison')
                                                ->placeholder('Pick up'),
                                            Infolists\Components\TextEntry::make('received_at')
                                                ->label('Received At')
                                                ->dateTime()
                                                ->placeholder('Not yet received'),
                                        ]),
                                    Infolists\Components\TextEntry::make('purpose')
                                        ->label('Purpose')
                                        ->columnSpanFull(),
                                ]),
                            Infolists\Components\Tabs\Tab::make('Remarks')
                                ->schema([
                                    Infolists\Components\TextEntry::make('remarks')
                                        ->markdown()
                                        ->columnSpanFull()
                                        ->visible(fn ($record) => $record->remarks !== null),
                                ]),
                            Infolists\Components\Tabs\Tab::make('Attachments')
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('contents')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('control_number')
                                                ->label('Control Number'),
                                            Infolists\Components\TextEntry::make('copies')
                                                ->label('Copies'),
                                            Infolists\Components\TextEntry::make('pages_per_copy')
                                                ->label('Pages per Copy'),
                                            Infolists\Components\TextEntry::make('particulars')
                                                ->label('Particulars'),
                                            Infolists\Components\TextEntry::make('payee')
                                                ->label('Payee'),
                                            Infolists\Components\TextEntry::make('amount')
                                                ->label('Amount')
                                                ->money('PHP'),
                                        ])
                                        ->columns(2),
                                ]),
                        ]),
                ]),
        ];
    }
}
