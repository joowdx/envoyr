<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\DocumentResource\Pages;
use App\Filament\User\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\CreateAction;
use Filament\Infolists\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    //Display Documents only for the user's office
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('office_id', Auth::user()->office_id);
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
                Section::make('Document Details')
                    ->icon('heroicon-o-document-text')
                    ->columns(7)
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('Title'),
                        Infolists\Components\TextEntry::make('code')
                            ->label('Document Code'),
                        Infolists\Components\TextEntry::make('classification.name')
                            ->label('Classification'),
                        Infolists\Components\TextEntry::make('source.name')
                            ->label('Source'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('office.name')
                            ->label('Office Origin')
                            ->formatStateUsing(function ($state, $record) {
                                return $state . ' (' . $record->section->name . ')';
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ]),
                Section::make('Transmittal Details')
                    ->icon('heroicon-o-map')
                    ->columns(3)
                    ->schema([]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title'),
                Tables\Columns\TextColumn::make('classification.name')
                    ->label('Classification'),
                Tables\Columns\TextColumn::make('source.name')
                    ->label('Source'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make('trashed'),
                Tables\Filters\Filter::make('deactivated')
                    ->query(fn(Builder $query): Builder => $query->whereNull('deactivated_at'))
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
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
