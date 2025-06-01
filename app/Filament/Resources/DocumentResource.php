<?php

namespace App\Filament\Resources;

use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use App\Enums\UserRole;
use App\Filament\Actions\Tables\UnpublishAction;
use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function canView(Model $record): bool
    {
        return !$record->trashed();
    }

    public static function canEdit(Model $record): bool
    {
        return !$record->trashed() && $record->isDraft();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::Make(1)
                    ->schema([
                        Forms\Components\Toggle::make('dissemination')
                            ->inline()
                            ->rule('required')
                            ->markAsRequired(),
                        Forms\Components\TextInput::make('title')
                            ->rule('required')
                            ->markAsRequired()
                            ->maxLength(255),
                        Forms\Components\Select::make('classification_id')
                            ->label('Classification')
                            ->relationship('classification', 'name')
                            ->searchable()
                            ->preload()
                            ->rule('required')
                            ->markAsRequired()
                            ->native(false)
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->slideOver()
                                    ->modalWidth('md');
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->rule('required')
                                    ->markAsRequired(),
                            ]),
                        Forms\Components\Select::make('source_id')
                            ->relationship('source', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->slideOver()
                                    ->modalWidth('md');
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->rule('required')
                                    ->markAsRequired(),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Document Information')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->extraAttributes(['class' => 'font-mono'])
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500)
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('title')
                            ->columnSpan(4)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'published' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->columnSpan(2),
                    ])
                    ->columns(8),

                Section::make('Classification')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Infolists\Components\TextEntry::make('classification.name')
                            ->label('Classification')
                            ->columnSpan(3),
                        Infolists\Components\TextEntry::make('source.name')
                            ->label('Source')
                            ->columnSpan(3),
                    ])
                    ->columns(6),

                Section::make('Origin Information')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Infolists\Components\TextEntry::make('office.name')
                            ->label('Office Origin')
                            ->columnSpan(6)
                            ->formatStateUsing(function ($state, $record) {
                                return $state.' ('.$record->section->name.')';
                            }),
                    ])
                    ->columns(6),

                Section::make('Metadata')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Created By')
                            ->columnSpan(3),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime()
                            ->columnSpan(3),
                        Infolists\Components\TextEntry::make('publishedBy.name')
                            ->label('Published By')
                            ->columnSpan(3)
                            ->visible(fn (Document $record): bool => $record->isPublished()),
                        Infolists\Components\TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime()
                            ->columnSpan(3)
                            ->visible(fn (Document $record): bool => $record->isPublished()),
                    ])
                    ->columns(6),

                Section::make('Transmittal Details')
                    ->icon('heroicon-o-map')
                    ->schema([])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->limit(60)
                    ->tooltip(fn (Tables\Columns\TextColumn $column): ?string => $column->getState()),
                Tables\Columns\TextColumn::make('classification.name')
                    ->label('Classification'),
                Tables\Columns\TextColumn::make('source.name')
                    ->label('Source'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (Document $record): string => $record->isPublished() ? 'success' : 'gray')
                    ->formatStateUsing(fn (Document $record): string => $record->isPublished() ? 'Published' : 'Draft')
                    ->getStateUsing(fn (Document $record): string => $record->isPublished() ? 'published' : 'draft'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make('trashed'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => match ($value) {
                                'draft' => $query->whereNull('published_at'),
                                'published' => $query->whereNotNull('published_at'),
                                default => $query,
                            }
                        );
                    }),
            ])
            ->actions([
                UnpublishAction::make()
                    ->visible(fn (Document $record): bool => $record->isPublished()),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Document $record): bool => $record->isDraft()),                    
                Tables\Actions\ViewAction::make(), 
                Tables\Actions\Action::make('generateQR')
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
                        Tables\Actions\Action::make('download')
                            ->label('Download QR')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(function (Document $record) {
                                $base64 = (new DownloadQR)($record);

                                return Response::streamDownload(
                                    function () use ($base64) {
                                        echo base64_decode($base64);
                                    },
                                    'qr-code.pdf',
                                    ['Content-Type' => 'application/pdf']
                                );
                            }),
                    ]),
        
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
            'view' => Pages\ViewDocument::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->when(Auth::user()->role !== UserRole::ROOT, function (Builder $query) {
                $query->where('office_id', Auth::user()->office_id);
            });
    }
}
