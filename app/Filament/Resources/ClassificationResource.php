<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Classification;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Filament\Resources\ClassificationResource\Pages;
use App\Filament\Resources\ClassificationResource\RelationManagers;
use App\Filament\Resources\ClassificationResource\RelationManagers\DocumentRelationManager;
use Filament\Actions\ViewAction;

class ClassificationResource extends Resource
{
    protected static ?string $model = Classification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name') 
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->placeholder('Enter classification name'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(255)
                    ->placeholder('Enter classification description')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name') 
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('description') 
                    ->limit(100)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at') 
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            DocumentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassifications::route('/'),
            'view' => Pages\ViewClassification::route('/{record}'),
        ];
    }
}
