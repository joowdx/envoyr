<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->markAsRequired()
                    ->columnSpanFull(),
                    
                Select::make('classification_id')
                    ->label('Classification')
                    ->relationship('classification', 'name')
                    ->searchable()
                    ->preload()
                    ->rule('required')
                    ->markAsRequired()
                    ->native(false)
                    ->createOptionAction(function ($action) {
                        return $action
                            ->slideOver()
                            ->modalWidth('md');
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->rule('required')
                            ->markAsRequired(),
                    ]),
                    
                Select::make('source_id')
                    ->label('Source')
                    ->relationship('source', 'name')
                    ->preload()
                    ->searchable()
                    ->createOptionAction(function ($action) {
                        return $action
                            ->slideOver()
                            ->modalWidth('md');
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->rule('required')
                            ->markAsRequired(),
                    ]),
                    
                Toggle::make('electronic')
                    ->label('Electronic Document')
                    ->helperText('Document is in electronic format'),
                    
                Toggle::make('dissemination')
                    ->label('For Dissemination')
                    ->helperText('Document is for general dissemination'),
            ]);
    }
}
