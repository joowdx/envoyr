<?php

namespace App\Filament\Resources\Documents\Widgets;

use App\Models\Document;
use App\Models\Transmittal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TransmittalHistoryTable extends BaseWidget
{
    public ?Document $record = null;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->record ? 
                $this->record->transmittals()->getQuery()->latest() : 
                Transmittal::query()->whereNull('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Transmittal Code')
                    ->searchable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('fromOffice.name')
                    ->label('From Office')
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('toOffice.name')
                    ->label('To Office')
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                    
                Tables\Columns\IconColumn::make('received_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->state(fn ($record) => !is_null($record->received_at)),
                    
                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Files')
                    ->counts('attachments')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Transmitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Transmittal History')
            ->description('Complete history of all transmittals for this document')
            ->emptyStateHeading('No transmittals')
            ->emptyStateDescription('This document has not been transmitted yet.')
            ->emptyStateIcon('heroicon-o-paper-airplane')
            ->striped();
    }
}
