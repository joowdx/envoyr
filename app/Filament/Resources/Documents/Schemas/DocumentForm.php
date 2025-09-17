<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
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
                    ])
                    ->columnSpan(1),
                    
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
                    ])
                    ->columnSpan(1),
                    
                Toggle::make('electronic')
                    ->label('Electronic Document')
                    ->helperText('Document is in electronic format')
                    ->columnSpan(1),
                    
                Toggle::make('dissemination')
                    ->label('For Dissemination')
                    ->helperText('Document is for general dissemination')
                    ->columnSpan(1),
                    
                Repeater::make('contents')
                    ->addActionLabel('Add Content')
                    ->columnSpanFull()
                    ->orderColumn('sort')
                    ->hint('Specify the contents/attachments for this document')
                    ->helperText('Add files, documents, or other content items')
                    ->itemLabel(fn ($state) => $state['title'] ?? 'New Content')
                    ->collapsed()
                    ->schema([
                        TextInput::make('title')
                            ->label('Content Title')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Enter a descriptive title for this content item'),
                        
                        TextInput::make('context.control')
                            ->label('Control #')
                            ->columnSpan(1)
                            ->helperText('Control number'),
                            
                        TextInput::make('context.pages')
                            ->label('Pages')
                            ->type('number')
                            ->minValue(1)
                            ->columnSpan(1)
                            ->helperText('Number of pages'),
                            
                        TextInput::make('context.copies')
                            ->label('Copies')
                            ->type('number')
                            ->minValue(1)
                            ->columnSpan(1)
                            ->helperText('Number of copies'),
                        
                        TextInput::make('context.particulars')
                            ->label('Particulars')
                            ->columnSpan(1)
                            ->helperText('Additional details'),
                            
                        TextInput::make('context.payee')
                            ->label('Payee')
                            ->columnSpan(1)
                            ->helperText('Payment recipient'),
                            
                        TextInput::make('context.amount')
                            ->label('Amount')
                            ->type('number')
                            ->step('0.01')
                            ->minValue(0)
                            ->columnSpan(1)
                            ->helperText('Monetary amount'),
                        
                        Textarea::make('context.remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Additional notes or remarks'),
                    ])
                    ->columns(3),
            ]);
    }
}
