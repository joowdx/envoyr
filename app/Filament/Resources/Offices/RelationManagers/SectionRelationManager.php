<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Select::make('user_id')
                    ->label('Section Head (User)')
                    ->options(function () {
                        $officeId = $this->getOwnerRecord()->id;
                        return \App\Models\User::where('office_id', $officeId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->placeholder('Select a user as section head (optional)')
                    ->nullable()
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        if ($state) {
                            $user = \App\Models\User::find($state);
                            if ($user) {
                                $set('head_name', $user->name);
                                $set('designation', $user->designation);
                            }
                        } else {
                            $set('head_name', null);
                            $set('designation', null);
                        }
                    }),
                
                TextInput::make('head_name')
                    ->label('Head Name')
                    ->maxLength(255)
                    ->required(fn ($get): bool => is_null($get('user_id')))
                    ->disabled(fn ($get): bool => !is_null($get('user_id')))
                    ->reactive(),
                
                TextInput::make('designation')
                    ->label('Designation')
                    ->maxLength(255)
                    ->required(fn ($get): bool => is_null($get('user_id')))
                    ->disabled(fn ($get): bool => !is_null($get('user_id')))
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('head_name')
                    ->label('Head Name'),
                TextColumn::make('designation')
                    ->label('Designation'),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Members'),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    
                    ->label('Add Section'),
            ])
            ->recordActions([
                EditAction::make()
                ->slideOver(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DetachBulkAction::make(),
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
