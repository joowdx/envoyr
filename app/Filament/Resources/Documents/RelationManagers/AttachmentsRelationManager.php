<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use App\Filament\Forms\Components\Counter;
use App\Models\Attachment;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static bool $canCreateAnother = false;

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('contents')
                    ->relationship('contents')
                    ->label('Files')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->label('File Title')
                            ->columnSpanFull(),
                        Counter::make('copies')
                            ->label('Number of Copies')
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'view'),
                        Counter::make('pages_per_copy')
                            ->label('Pages per Copy')
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'view'),

                        // Document Fields
                        TextInput::make('control_number')
                            ->label('Control Number'),

                        TextInput::make('particulars')
                            ->label('Particulars'),

                        TextInput::make('payee')
                            ->label('Payee'),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->step(0.01),

                        FileUpload::make('file')
                            ->multiple()
                            ->label('Upload Files')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->directory('attachments')
                            ->visibility('private')
                            ->visible(fn (): bool => $this->getOwnerRecord()->electronic),
                        Hidden::make('sort')
                            ->default(0),
                        Textarea::make('context')
                            ->label('Notes/Context')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->orderColumn('sort')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Untitled File')
                    ->addActionLabel('Add Attachment')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('contents_count')
                    ->label('Files Count')
                    ->counts('contents')
                    ->badge(),
                TextColumn::make('contents.title')
                    ->label('File Titles')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
                TextColumn::make('contents.copies')
                    ->label('Copies')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('contents.pages_per_copy')
                    ->label('Pages/Copy')
                    ->badge()
                    ->separator(', '),
                // Document Columns
                TextColumn::make('contents.control_number')
                    ->label('Control Number')
                    ->listWithLineBreaks()
                    ->limitList(2),
                TextColumn::make('contents.particulars')
                    ->label('Particulars')
                    ->listWithLineBreaks()
                    ->limit(30),
                TextColumn::make('contents.payee')
                    ->label('Payee')
                    ->listWithLineBreaks(),
                TextColumn::make('contents.amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->listWithLineBreaks(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Attachment')
                    ->createAnother(false)
                    ->slideOver()
                    ->modalHeading('Add New Attachment')
                    ->modalWidth('lg')
                    ->mutateDataUsing(function (array $data): array {
                        // Ensure the attachment is created as a draft (no transmittal_id)
                        $data['transmittal_id'] = null;

                        return $data;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->slideOver()
                        ->modalWidth('lg'),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                // Only show draft attachments (not transmittal attachments)
                return $query->whereNull('transmittal_id');
            });
    }

    public function isReadOnly(): bool
    {
        $document = $this->getOwnerRecord();

        if ($document->activeTransmittal()->exists()) {
            return true;
        }

        return ! $document->isOwnedByOffice(Auth::user()->office_id);
    }
}
