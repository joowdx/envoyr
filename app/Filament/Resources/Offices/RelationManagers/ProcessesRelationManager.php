<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Offices\Schemas\ProcessInfolist;
use App\Filament\Resources\Offices\Actions\ProcessWorkflowActions;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';
    protected static ?string $recordTitleAttribute = 'name';

    public function getTabLabel(): string
    {
        return 'Document Process Workflows';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        return \App\Models\Classification::find($record->classification_id)?->name ?? 'No classification';
                    }),
                    
                TextColumn::make('classification_name')
                    ->label('Classification')
                    ->getStateUsing(function ($record) {
                        return \App\Models\Classification::find($record->classification_id)?->name ?? 'No Classification';
                    })
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                    
                TextColumn::make('action_sequence_count')
                    ->label('Action Steps')
                    ->getStateUsing(function ($record) {
                        $sequence = json_decode($record->action_sequence ?? '[]', true);
                        return count($sequence) . ' step(s)';
                    })
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Process Workflows')
            ->emptyStateDescription('Create document processing workflows for specific classifications.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->recordTitleAttribute('name')
            ->headerActions([
                ProcessWorkflowActions::createWizardAction($this->ownerRecord),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading(fn ($record) => "Process: {$record->name}")
                        ->modalDescription('View document process workflow details')
                        ->modalWidth('3xl')
                        ->schema(fn (): array => ProcessInfolist::schema()),
                        
                    ProcessWorkflowActions::editWorkflowAction($this->ownerRecord),
                    
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Process Workflow')
                        ->modalDescription('Are you sure you want to delete this workflow? This action cannot be undone.')
                        ->hidden(fn () => !Auth::user()->can('delete', $this->ownerRecord)),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id(); 
        $data['office_id'] = $this->ownerRecord->id;
        
        return $data;
    }
}
