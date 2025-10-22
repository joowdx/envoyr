<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Models\ActionType;
use App\Models\Process;
use App\Services\ActionTopologicalSorter;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProcessesRelationManager extends RelationManager
{
    protected static string $relationship = 'processes';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Process Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Budget Review Process'),

                Select::make('classification_id')
                    ->label('Document Classification')
                    ->relationship('classification', 'name')
                    ->required()
                    ->placeholder('Select document classification'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('classification.name')
                    ->label('Classification')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions($this->getHeaderActions())
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('office_id', $this->ownerRecord->id))
            ->defaultSort('created_at', 'desc');
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['office_id'] = $this->ownerRecord->id;

                    return $data;
                })
                ->after(function (Process $record) {
                    $this->associateAllActionsToProcess($record);
                }),
        ];
    }

    /**
     * Automatically associate all office actions to the process with proper ordering
     */
    private function associateAllActionsToProcess(Process $process): void
    {
        // Get all active actions for this office
        $actions = ActionType::where('office_id', $process->office_id)
            ->where('is_active', true)
            ->with('prerequisites')
            ->get();

        if ($actions->isEmpty()) {
            return;
        }

        // Use the dedicated service for topological sorting
        $sorter = new ActionTopologicalSorter();
        $orderedActionIds = $sorter->sortByKahnsAlgorithm($actions);

        // Associate actions to process with sequence order
        $pivotData = [];
        foreach ($orderedActionIds as $index => $actionId) {
            $pivotData[$actionId] = ['sequence_order' => $index + 1];
        }

        $process->actions()->sync($pivotData);
    }



    public function isReadOnly(): bool
    {
        return false; // Allow all users to create processes for now
    }
}
