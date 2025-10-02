<?php
// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Actions/ProcessWorkflowActions.php

namespace App\Filament\Resources\Offices\Actions;

use App\Models\Process;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Resources\Offices\Schemas\ProcessWorkflowSchema;

class ProcessWorkflowActions
{
    public static function createWizardAction($ownerRecord): Action
    {
        return Action::make('process_workflow_wizard')
            ->label('Define Process Workflow')
            ->icon('heroicon-s-cog-6-tooth')
            ->modalHeading('Document Process Workflow Designer')
            ->modalDescription('Create a sequential workflow process for document classifications.')
            ->modalWidth('4xl')
            ->schema(ProcessWorkflowSchema::wizardSchema($ownerRecord))
            ->action(function (array $data) use ($ownerRecord) {
                self::createProcessWorkflow($data, $ownerRecord);
            })
            ->modalSubmitActionLabel('Create Process Workflow')
            ->modalSubmitAction(fn (Action $action) => 
                $action->icon('heroicon-s-cog-6-tooth')
            );
    }

    public static function editWorkflowAction($ownerRecord): Action
    {
        return Action::make('edit_workflow')
            ->label('Edit Workflow')
            ->icon('heroicon-s-pencil')
            ->color('warning')
            ->modalHeading(fn ($record) => "Edit Process: {$record->name}")
            ->modalDescription('Modify the sequential workflow for this process')
            ->modalWidth('3xl')
            ->fillForm(function ($record) {
                $actionSequence = json_decode($record->action_sequence ?? '[]', true);
                return [
                    'process_name' => $record->name,
                    'classification_id' => $record->classification_id,
                    'process_description' => $record->description,
                    'action_workflow' => $actionSequence,
                ];
            })
            ->schema(ProcessWorkflowSchema::editSchema($ownerRecord))
            ->action(function ($record, array $data) use ($ownerRecord) {
                self::updateProcessWorkflow($record, $data);
            });
    }

    /**
     * Create process workflow according to Document Tracking System
     */
    private static function createProcessWorkflow(array $data, $ownerRecord): void
    {
        $actionWorkflow = $data['action_workflow'] ?? [];
        
        if (empty($actionWorkflow)) {
            Notification::make()
                ->title('No Workflow Steps Defined')
                ->body('Please define at least one workflow step.')
                ->danger()
                ->send();
            return;
        }

        // Sort workflow by step_order per Document Tracking System requirements
        usort($actionWorkflow, function ($a, $b) {
            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
        });

        // Create process workflow record per ER diagram Process entity
        Process::create([
            'name' => $data['process_name'],
            'classification_id' => $data['classification_id'],
            'description' => $data['process_description'] ?? null,
            'office_id' => $ownerRecord->id,
            'user_id' => Auth::id(),
            'action_sequence' => json_encode($actionWorkflow),
            'status' => 'Draft', // Initial status for process templates
            'processed_at' => now(),
        ]);

        Notification::make()
            ->title('Process Workflow Created')
            ->body("Created workflow '{$data['process_name']}' with " . count($actionWorkflow) . " sequential steps.")
            ->success()
            ->send();
    }

    /**
     * Update process workflow per Document Tracking System
     */
    private static function updateProcessWorkflow($record, array $data): void
    {
        $actionWorkflow = $data['action_workflow'] ?? [];
        
        // Sort workflow by step_order
        usort($actionWorkflow, function ($a, $b) {
            return ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0);
        });

        $record->update([
            'name' => $data['process_name'],
            'classification_id' => $data['classification_id'],
            'description' => $data['process_description'] ?? null,
            'action_sequence' => json_encode($actionWorkflow),
        ]);

        Notification::make()
            ->title('Process Workflow Updated')
            ->body("Updated workflow '{$data['process_name']}' with " . count($actionWorkflow) . " sequential steps.")
            ->success()
            ->send();
    }
}