<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
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
                
                Section::make('Document Attachments')
                    ->columnSpanFull()
                    ->description('Manage the files and contents attached to this document')
                    ->schema([
                        Repeater::make('contents')
                            ->addActionLabel('Add content')
                            ->columnSpanFull()
                            ->hint('Specify the contents enclosed with the document')
                            ->helperText('What are the files or documents attached?')
                            ->itemLabel(fn ($state) => $state['title'] ?? 'Untitled Content')
                            ->collapsed()
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->columnSpanFull()
                                    ->placeholder('Enter content title'),
                            ]),
                    ]),
            ]);
    }
}
