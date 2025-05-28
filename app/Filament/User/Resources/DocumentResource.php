<?php

namespace App\Filament\User\Resources;

use App\Actions\DownloadQR;
use App\Actions\GenerateQR;
use App\Filament\User\Resources\DocumentResource\Pages;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Display Documents only for the user's office
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('office_id', Auth::user()->office_id)
            ->with(['classification', 'source', 'user', 'office', 'section']);
    }

    // Prevent users from viewing a document if it has been deleted
    public static function canView(Model $record): bool
    {
        return ! $record->trashed();
    }

    // Prevent users from editing a document if it has been deleted
    public static function canEdit(Model $record): bool
    {
        return ! $record->trashed();
    }

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Grid::Make(1)
                    ->schema([

                        Forms\Components\Toggle::make('directive')
                            ->label('Directive')
                            ->inline()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('classification_id')
                            ->label('Classification')
                            ->relationship('classification', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->createOptionAction(function (Action $action) {
                                return $action->slideOver();
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Classification Name')
                                    ->required(),
                            ]),

                        Forms\Components\Select::make('source_id')
                            ->relationship('source', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Source Name')
                                    ->required(),
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
                            ->columnSpan(6)
                            ->weight('bold'),
                    ])
                    ->columns(6),

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
                Tables\Filters\Filter::make('deactivated')
                    ->query(fn (Builder $query): Builder => $query->whereNull('deactivated_at'))
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('generateQR')
                    ->label('Generate QR')
                    ->icon('heroicon-o-qr-code')
                    ->modalWidth('md')
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
        return [

        ];
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
}
