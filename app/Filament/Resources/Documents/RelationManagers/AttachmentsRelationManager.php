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
                    ->label('Files')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('File Title')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file')
                            ->multiple()
                            ->label('Upload Files')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->directory('attachments')
                            ->visibility('private'),
                        Forms\Components\Hidden::make('sort')
                            ->default(0),
                        Forms\Components\Textarea::make('context')
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
                        // Ensure the attachment is created as a draft (no transmittal_id)
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
                // Only show draft attachments (not transmittal attachments)
                return $query->whereNull('transmittal_id');
            });
    }

    public function isReadOnly(): bool
    {
        // Make read-only if document has active transmittal
        return $this->getOwnerRecord()->activeTransmittal()->exists();
    }
}