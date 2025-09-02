<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Document;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Actions\Concerns\TransmittalHistoryInfolist;
class DocumentInfolist
{
    use TransmittalHistoryInfolist;
    public static function configure($schema)
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columns(2)
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextEntry::make('title')
                            ->columnSpanFull()
                            ->weight('bold'),
                        TextEntry::make('code')
                            ->extraAttributes(['class' => 'font-mono'])
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500),
                        TextEntry::make('classification.name')
                            ->label('Classification'),
                    ]),
                Section::make('Source Origin')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextEntry::make('office.name')
                            ->label('Office Source')
                            ->columnSpan(3),
                        TextEntry::make('source.name')
                            ->label('External Source')
                            ->placeholder('None')
                            ->columnSpan(3),
                    ]),
                Section::make('Metadata')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Created By'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime()
                            ->visible (fn (Document $record): bool => $record->isPublished()),
                    ]),
                Section::make('Document History')
                    ->icon('heroicon-o-paper-airplane')
                    ->extraAttributes([
                        'x-ref' => 'documentHistory',
                        'x-init' => <<<'JS'
                            $refs.documentHistory
                                .querySelector('nav')
                                .removeAttribute('class')

                            $refs.documentHistory
                                .querySelector('ul')
                                .classList
                                .add(
                                    'border-b',
                                    'flex',
                                    'gap-x-1',
                                    'p-2',
                                    'dark:border-white/10',
                                )
                            $refs.documentHistory
                                .querySelector('nav')
                                .style.paddingRight = '0px'
                            
                            $refs.documentHistory.children[1].firstElementChild.style.paddingTop = '0px';
                            JS,
                    ])
                    ->schema(self::getTransmittalHistorySchema()),

            ]);
    }
}