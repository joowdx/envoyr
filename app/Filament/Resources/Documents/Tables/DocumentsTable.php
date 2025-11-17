<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Filament\Actions\PublishDocumentAction;
use App\Filament\Actions\TransmitDocumentAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('classification.name')
                    ->label('Classification'),
                TextColumn::make('source.name')
                    ->label('Source'),
                TextColumn::make('contents.control_number')
                    ->label('Control Number')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->placeholder('—'),
                TextColumn::make('contents.particulars')
                    ->label('Particulars')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('contents.payee')
                    ->label('Payee')
                    ->placeholder('—'),
                TextColumn::make('contents.amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->placeholder('—'),
                TextColumn::make('contents.copies')
                    ->label('Copies')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('contents.pages_per_copy')
                    ->label('Pages/Copy')
                    ->badge()
                    ->placeholder('—'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    TransmitDocumentAction::make(),
                    PublishDocumentAction::make(),
                    EditAction::make(),
                    ViewAction::make(),
                ]),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
