<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Filament\Forms\Components\Counter;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // Document Information Section
                TextInput::make('title')
                    ->label('Document Title')
                    ->required()
                    ->markAsRequired()
                    ->placeholder('Enter the document title')
                    ->columnSpan(2),

                Select::make('classification_id')
                    ->label('Document Classification')
                    ->relationship('classification', 'name')
                    ->searchable()
                    ->preload()
                    ->rule('required')
                    ->markAsRequired()
                    ->native(false)
                    ->placeholder('Select or create classification')
                    ->createOptionAction(function ($action) {
                        return $action
                            ->slideOver()
                            ->modalWidth('md');
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Classification Name')
                            ->rule('required')
                            ->markAsRequired()
                            ->placeholder('e.g., Memo, Report, Invoice'),
                    ]),

                Select::make('source_id')
                    ->label('Document Source')
                    ->relationship('source', 'name')
                    ->preload()
                    ->searchable()
                    ->placeholder('Select or create source (optional)')
                    ->createOptionAction(function ($action) {
                        return $action
                            ->slideOver()
                            ->modalWidth('md');
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Source Name')
                            ->rule('required')
                            ->markAsRequired()
                            ->placeholder('e.g., External Agency, Department'),
                    ]),

                // Document Properties Section
                Toggle::make('electronic')
                    ->label('Electronic Document')
                    ->helperText('Check if document is in digital format')
                    ->inline(false),

                Toggle::make('dissemination')
                    ->label('For Dissemination')
                    ->helperText('Check if document is for public distribution')
                    ->inline(false),

                // Attachments Section
                Repeater::make('contents')
                    ->label('Document Attachments')
                    ->schema([
                        // Basic Information
                        TextInput::make('title')
                            ->label('Attachment Title')
                            ->required()
                            ->placeholder('e.g., Budget Report Q4, Meeting Minutes')
                            ->helperText('Add files and details for this document')
                            ->columnSpan(2),

                        // Quantity Fields
                        Counter::make('copies')
                            ->label('Number of Copies')
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('How many copies?'),

                        Counter::make('pages_per_copy')
                            ->label('Pages per Copy')
                            ->minValue(1)
                            ->maxValue(1000)
                            ->helperText('Pages in each copy'),

                        // Financial & Control Fields
                        TextInput::make('control_number')
                            ->label('Control Number')
                            ->placeholder('e.g., CTRL-2025-001')
                            ->helperText('Document tracking number (optional)'),

                        TextInput::make('payee')
                            ->label('Payee')
                            ->placeholder('e.g., Contractor Name, Supplier')
                            ->helperText('Who receives payment (if applicable)'),

                        TextInput::make('particulars')
                            ->label('Particulars')
                            ->placeholder('Brief description of the content')
                            ->columnSpan(2)
                            ->helperText('What this attachment is about'),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->helperText('Monetary value (if applicable)'),

                        // File Upload
                        FileUpload::make('file')
                            ->label('Upload Files')
                            ->multiple()
                            ->preserveFilenames()
                            ->directory('attachments')
                            ->visibility('private')
                            ->acceptedFileTypes(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png'])
                            ->maxSize(10240) // 10MB
                            ->columnSpan(2)
                            ->visible(fn (callable $get) => $get('../../electronic'))
                            ->helperText('Upload digital files (PDF, Word, Excel, Images - Max 10MB each)'),

                        // Additional Notes
                        Textarea::make('context')
                            ->label('Additional Notes')
                            ->placeholder('Any additional context or notes about this attachment')
                            ->rows(3)
                            ->columnSpan(2)
                            ->helperText('Optional notes for reference'),

                        Hidden::make('sort')
                            ->default(0),
                    ])
                    ->columns(2)
                    ->orderColumn('sort')
                    ->collapsible()
                    ->cloneable()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Attachment')
                    ->addActionLabel('+ Add Another Attachment')
                    ->deleteAction(
                        fn ($action) => $action->requiresConfirmation()
                    )
                    ->columnSpan(2)
                    ->defaultItems(1)
                    ->minItems(1),
            ]);
    }
}
