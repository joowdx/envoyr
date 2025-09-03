<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Models\Document;
use Filament\Tables\Table;
use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Response;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;


use App\Filament\Actions\Tables\ReceiveDocumentAction;
use App\Filament\Actions\Tables\TransmitDocumentAction;
use App\Filament\Actions\Tables\UnpublishDocumentAction;

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
                TransmitDocumentAction::make(),
                ReceiveDocumentAction::make()
                    ->label('Receive Document'),
                Action::make('generateQR')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->modalWidth('md')
                    ->visible(fn (Document $record): bool => $record->isPublished())
                    ->modalContent(function (Document $record) {
                        $qrCode = (new GenerateQR)($record->code);

                        return view('components.qr-code', [
                            'qrCode' => $qrCode,
                            'code' => $record->code,
                        ]);
                    })
                    ->modalFooterActions([
                        Action::make('download')
                            ->label('Download QR Code')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(function (Document $record) {
                                $base64 = (new DownloadQR)($record->code);

                                return Response::streamDownload(
                                    function () use ($base64) {
                                        echo base64_decode($base64);
                                    },
                                    'qr-code.pdf',
                                    ['Content-Type' => 'application/pdf']
                                );
                            }),
                        ]),
                    ViewAction::make('view'),
                    ActionGroup::make([
                        UnpublishDocumentAction::make(),
                        EditAction::make()
                            ->visible(fn (Document $record): bool => $record->isDraft()),
                        RestoreAction::make(),
                        ForceDeleteAction::make(),

                    ])

            ]);
    }
}
