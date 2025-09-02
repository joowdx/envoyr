<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Models\Document;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\ForceDeleteBulkAction;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->extraAttributes(['class' => 'font-mono'])
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                TextColumn::make('classification')
                    ->label('Classification')
                    ->searchable(),
                TextColumn::make('source.name')
                    ->label('Source')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (Document $record): string => $record->isPublished() ? 'success' : 'gray')
                    ->formatStateUsing(fn (Document $record): string => $record->isPublished() ? 'Published' : 'Draft')
                    ->getStateUsing(fn (Document $record): string => $record->isPublished() ? 'Published' : 'Draft'),
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Create at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->placeholder('All')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published'
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            @$data['value'],
                            fn (Builder $query, $value): Builder => match ($value) {
                                'draft' => $query->whereNull('published_at'),
                                'published' => $query->whereNotNull('published_at'),
                                default => $query,
                            }
                        );
                    })
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
