<?php

namespace App\Filament\Resources;

use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use App\Enums\UserRole;
use App\Filament\Actions\Tables\ReceiveDocumentAction;
use App\Filament\Actions\Tables\TransmitDocumentAction;
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
        return ! $record->trashed();
    }

    public static function canEdit(Model $record): bool
    {
        return ! $record->trashed() && $record->isDraft();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->rule('required')
                    ->markAsRequired()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->hint('Add a descriptive title for the document')
                    ->helperText('What is the document about?'),
                Forms\Components\Select::make('classification_id')
                    ->label('Classification')
                    ->relationship('classification', 'name')
                    ->searchable()
                    ->preload()
                    ->rule('required')
                    ->markAsRequired()
                    ->native(false)
                    ->hint('Classify the document for better organization')
                    ->helperText('Is this a memorandum, invitation, request, etc.?')
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
                    ->hint('Select the source of the document if it is from an external entity')
                    ->helperText('Is this from COA, DILG, DICT, etc.?')
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
                Forms\Components\Grid::make()
                    ->relationship('enclosure')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('attachments')
                            ->relationship()
                            ->addActionLabel('Add attachment')
                            ->columnSpanFull()
                            ->orderColumn('sort')
                            ->hint('Specify the attachments enclosed within the document')
                            ->helperText('What are the files or documents attached?')
                            ->itemLabel(fn ($state) => $state['title'])
                            ->collapsed()
                            ->required()
                            ->schema([
                                Forms\Components\Toggle::make('electronic')
                                    ->hidden(),
                                Forms\Components\TextInput::make('title')
                                    ->rule('required')
                                    ->markAsRequired()
                                    ->hidden(fn (callable $get) => $get('electronic')),
                                Forms\Components\Grid::make(3)
                                    ->hidden(fn (callable $get) => $get('electronic'))
                                    ->schema([
                                        Forms\Components\TextInput::make('context.control')
                                            ->label('Control #'),
                                        Forms\Components\TextInput::make('context.pages')
                                            ->minValue(1)
                                            ->rule('numeric'),
                                        Forms\Components\TextInput::make('context.copies')
                                            ->minValue(1)
                                            ->rule('numeric'),
                                        Forms\Components\TextInput::make('context.particulars'),
                                        Forms\Components\TextInput::make('context.payee'),
                                        Forms\Components\TextInput::make('context.amount')
                                            ->minValue(1)
                                            ->rule('numeric'),
                                    ]),
                                Forms\Components\Textarea::make('remarks')
                                    ->hidden(fn (callable $get) => $get('electronic'))
                                    ->maxLength(4096),
                            ])
                    ])
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Document Information')
                    ->columns(2)
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->columnSpanFull()
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('code')
                            ->extraAttributes(['class' => 'font-mono'])
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500),
                        Infolists\Components\TextEntry::make('classification.name')
                            ->label('Classification'),
                    ]),
                Section::make('Source Origin')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Infolists\Components\TextEntry::make('office.name')
                            ->label('Office Source')
                            ->columnSpan(3),
                        Infolists\Components\TextEntry::make('source.name')
                            ->label('External Source')
                            ->placeholder('None')
                            ->columnSpan(3),
                    ])
                    ->columns(6),
                Section::make('Metadata')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime()
                            ->visible(fn (Document $record): bool => $record->isPublished()),
                    ])
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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->placeholder('All')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
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
                    }),
                Tables\Filters\TrashedFilter::make('trashed'),
            ])
            ->actions([
                TransmitDocumentAction::make(),
                ReceiveDocumentAction::make()
                    ->label('Receive'),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    UnpublishAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Document $record): bool => $record->isDraft()),
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
