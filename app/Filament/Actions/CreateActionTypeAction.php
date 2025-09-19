<?php

namespace App\Filament\Actions;

use App\Models\ActionType;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

class CreateActionTypeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'createActionType';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Create New Action Type')
            ->icon('heroicon-o-plus')
            ->modalHeading('Create Action Type')
            ->modalWidth('md')
            ->form([
                TextInput::make('name')
                    ->label('Action Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Review, Approve, Process'),
                TextInput::make('status_name')
                    ->label('Document Status')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Under Review, Approved, Processing'),
            ])
            ->action(function (array $data) {
                Log::info('CreateActionTypeAction called with data:', $data);
                
                $officeId = $this->getLivewire()->ownerRecord->id;
                Log::info('Office ID:', ['office_id' => $officeId]);
                
                $actionType = ActionType::create([
                    'office_id' => $officeId,
                    'name' => $data['name'],
                    'status_name' => $data['status_name'],
                    'is_active' => true,
                ]);
                
                Log::info('ActionType created:', ['id' => $actionType->id]);
                
                // Optionally refresh the page or show success notification
                $this->success();
            });
    }
}