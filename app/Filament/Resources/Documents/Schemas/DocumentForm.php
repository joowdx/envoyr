<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->rule('required')
                    ->markAsRequired()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->hint('Add a descriptive title for the document')
                    ->helperText('What is the document about?'),
                Select::make('classification_id')
                    ->label('Classification')
                    ->relationship('classification', 'name')
                    ->searchable()
                    ->preload()
                    ->rule('required')
                    ->markAsRequired()
                    ->native(false)
                    ->hint('Classify the document for better organization')
                    ->helperText('Is this a memorandum, invitation, request, etc.?')
                    ->createOptionAction(fn (Action $action) => $action
                        ->slideOver()
                        ->modalWidth('md')
                    )
                    ->createOptionForm([
                        TextInput::make('name')
                            ->rule('required')
                            ->markAsRequired(),
                    ]),
                Select::make('source_id')
                    ->relationship('source', 'name')
                    ->preload()
                    ->searchable()
                    ->hint('Select the source of the document if it is from an external entity')
                    ->helperText('Was this received from COA, DILG, DICT, etc.?')
                    ->createOptionAction(fn (Action $action) => $action
                        ->slideOver()
                        ->modalWidth('md')
                    )
                    ->createOptionForm([
                        TextInput::make('name')
                            ->rule('required')
                            ->markAsRequired(),
                    ]),
                // Fixed: Removed invalid relationship from Grid and moved to Repeater
                Repeater::make('contents')
                    ->relationship('attachment')  // Specify the relationship here
                    ->addActionLabel('Add Content')
                    ->columnSpanFull()
                    ->orderColumn('sort')
                    ->hint('Specify the content enclosed with the document')
                    ->helperText('What are the files or documents attached?')
                    ->itemLabel(fn ($state) => $state['title'] ?? 'Untitled')  // Added null check
                    ->collapsed()
                    ->required()
                    ->schema([
                        // Removed hidden Toggle if not needed; add back if required
                        TextInput::make('title')
                            ->rule('required')
                            ->markAsRequired(),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('context.control')
                                    ->label('Control No.'),
                                TextInput::make('context.pages')
                                    ->minValue(1)
                                    ->rule('numeric'),
                                TextInput::make('context.copies')
                                    ->minValue(1)
                                    ->rule('numeric'),
                                TextInput::make('context.particulars'),
                                TextInput::make('context.payee'),
                                TextInput::make('context.amount')
                                    ->minValue(1)
                                    ->rule('numeric')
                                    ->maxLength(255),  // Fixed: Valid maxLength
                            ]),
                        Textarea::make('remarks')
                            ->maxLength(4096),
                    ]),
            ]);
    }
}
