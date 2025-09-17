<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use App\Models\Attachment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static bool $canCreateAnother = false;

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Repeater::make('contents')
                    ->relationship('contents')
                    ->label('Files / Contents')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('Content Title')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('file')
                            ->multiple()
                            ->label('Upload Files')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->directory('attachments')
                            ->visibility('private'),

                        Forms\Components\TextInput::make('context.control')
                            ->label('Control #'),
                        Forms\Components\TextInput::make('context.pages')
                            ->label('Pages')
                            ->type('number')
                            ->minValue(1),
                        Forms\Components\TextInput::make('context.copies')
                            ->label('Copies')
                            ->type('number')
                            ->minValue(1),

                        Forms\Components\TextInput::make('context.particulars')
                            ->label('Particulars'),
                        Forms\Components\TextInput::make('context.payee')
                            ->label('Payee'),
                        Forms\Components\TextInput::make('context.amount')
                            ->label('Amount')
                            ->type('number')
                            ->step('0.01')
                            ->minValue(0),

                        Forms\Components\Textarea::make('context.remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('sort')
                            ->default(0),
                    ])
                    ->columns(3)
                    ->orderColumn('sort')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Untitled')
                    ->addActionLabel('Add Content')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('contents_count')
                    ->label('Files Count')
                    ->counts('contents')
                    ->badge(),
                Tables\Columns\TextColumn::make('contents.title')
                    ->label('File Titles')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable(),
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
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['transmittal_id'] = null;
                        return $data;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('lg'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                return $query->whereNull('transmittal_id');
            });
    }

    public function isReadOnly(): bool
    {
        $document = $this->getOwnerRecord();
        
        if ($document->activeTransmittal()->exists()) {
            return true;
        }
        
        return !$document->isOwnedByOffice(auth()->user()->office_id);
    }
}