<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Filament\Actions\PublishDocumentAction;
use App\Filament\Actions\TransmitDocumentAction;
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
                TextColumn::make('code'),
                TextColumn::make('classification.name'),
                TextColumn::make('source.name'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                TransmitDocumentAction::make(),
                PublishDocumentAction::make(),
                EditAction::make(),
                ViewAction::make(),
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
