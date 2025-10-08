<?php
// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Actions/ProcessWorkflowActions.php

namespace App\Filament\Resources\Offices\Actions;

use App\Models\Process;
use App\Models\ActionType;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Resources\Offices\Schemas\SimpleWorkflowWizard;
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

    public static function createSimpleWorkflowAction($ownerRecord): Action
    {
        return Action::make('create_simple_workflow')
            ->label('Simple Workflow Wizard')
            ->icon('heroicon-s-sparkles')
            ->color('success')
            ->modalHeading('Simple Workflow Creator')
            ->modalDescription('Create a workflow in just a few easy steps')
            ->modalWidth('4xl')
            ->schema(SimpleWorkflowWizard::createSimpleWorkflow($ownerRecord))
            ->action(function (array $data) use ($ownerRecord) {
                self::createSimpleWorkflow($data, $ownerRecord);
            });
    }

    private static function createSimpleWorkflow(array $data, $ownerRecord): void
    {
        // Get action sequence from stepper data
        $actionSequenceJson = $data['action_sequence'] ?? '[]';
        $actionSequence = json_decode($actionSequenceJson, true) ?? [];
        
        if (empty($actionSequence)) {
            Notification::make()
                ->title('No Actions Selected')
                ->body('Please add at least one action to your workflow sequence using the stepper.')
                ->warning()
                ->send();
            return;
        }

        // Convert stepper data to workflow format
        $workflowSteps = [];
        foreach ($actionSequence as $index => $step) {
            $actionType = ActionType::find($step['action_type_id'] ?? $step['id'] ?? null);
            
            if ($actionType) {
                $workflowSteps[] = [
                    'action_type_id' => $actionType->id,
                    'step_order' => $index + 1,
                    'step_description' => "Perform {$actionType->name} - Document status becomes: {$actionType->status_name}",
                    'resulting_status' => $actionType->status_name,
                ];
            }
        }

        // Create Process workflow record per ER diagram
        $process = Process::create([
            'name' => $data['process_name'],
            'classification_id' => $data['classification_id'],
            'description' => $data['workflow_purpose'] ?? "Simple workflow with " . count($workflowSteps) . " sequential actions",
            'office_id' => $ownerRecord->id,
            'user_id' => Auth::id(),
            'action_sequence' => json_encode($workflowSteps),
            'status' => 'Active',
            'processed_at' => now(),
        ]);

        Notification::make()
            ->title('Simple Workflow Created Successfully!')
            ->body("Created '{$data['process_name']}' with " . count($workflowSteps) . " actions using the stepper interface.")
            ->success()
            ->duration(5000)
            ->send();
    }

    /**
     * Apply predefined workflow templates based on Document Tracking System patterns
     * Aligns with flowchart: Document Creation → Processing → Status Updates
     */
    private static function applyWorkflowTemplate(string $template, array $selectedActions, $ownerRecord): array
    {
        $actionSequence = [];
        
        // Get available actions for this office per ER diagram ActionType entity
        $availableActions = ActionType::where('office_id', $ownerRecord->id)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
        
        switch ($template) {
            case 'receive_review_approve':
                // Common document processing flow: Receive → Review → Approve
                $templateFlow = [
                    'receive' => ['Review', 'Receive', 'Accept'],
                    'review' => ['Review', 'Evaluate', 'Check'],
                    'approve' => ['Approve', 'Authorize', 'Confirm']
                ];
                break;
                
            case 'receive_process_forward':
                // Processing and forwarding flow per flowchart forwarding logic
                $templateFlow = [
                    'receive' => ['Receive', 'Accept', 'Log'],
                    'process' => ['Process', 'Handle', 'Execute'],
                    'forward' => ['Forward', 'Send', 'Transmit']
                ];
                break;
                
            case 'receive_approve_return':
                // Return to originating office flow per flowchart return logic
                $templateFlow = [
                    'receive' => ['Receive', 'Accept'],
                    'approve' => ['Approve', 'Authorize'],
                    'return' => ['Return', 'Send Back']
                ];
                break;
                
            default:
                // Custom template - use selected actions as-is
                return array_map(function ($actionId, $index) use ($availableActions) {
                    $action = $availableActions[$actionId] ?? null;
                    return [
                        'action_type_id' => $actionId,
                        'step_order' => $index + 1,
                        'step_description' => $action ? "Perform {$action->name}" : '',
                        'resulting_status' => $action?->status_name ?? 'Unknown',
                    ];
                }, $selectedActions, array_keys($selectedActions));
        }
        
        // Match selected actions to template flow
        $stepOrder = 1;
        foreach ($templateFlow as $stepType => $keywords) {
            foreach ($selectedActions as $actionId) {
                $action = $availableActions[$actionId] ?? null;
                if (!$action) continue;
                
                // Check if action name matches template keywords
                $actionName = strtolower($action->name);
                $matchFound = false;
                
                foreach ($keywords as $keyword) {
                    if (str_contains($actionName, strtolower($keyword))) {
                        $actionSequence[] = [
                            'action_type_id' => $actionId,
                            'step_order' => $stepOrder++,
                            'step_description' => self::getTemplateStepDescription($stepType, $action->name),
                            'resulting_status' => $action->status_name,
                            'template_step' => $stepType,
                        ];
                        $matchFound = true;
                        break 2; // Break both loops
                    }
                }
            }
        }
        
        // Add any remaining actions that didn't match template
        foreach ($selectedActions as $actionId) {
            $alreadyAdded = collect($actionSequence)->contains('action_type_id', $actionId);
            if (!$alreadyAdded) {
                $action = $availableActions[$actionId] ?? null;
                if ($action) {
                    $actionSequence[] = [
                        'action_type_id' => $actionId,
                        'step_order' => $stepOrder++,
                        'step_description' => "Additional step: {$action->name}",
                        'resulting_status' => $action->status_name,
                        'template_step' => 'additional',
                    ];
                }
            }
        }
        
        return $actionSequence;
    }

    /**
     * Generate step descriptions based on Document Tracking System workflow templates
     */
    private static function getTemplateStepDescription(string $stepType, string $actionName): string
    {
        return match($stepType) {
            'receive' => "Initial document reception and logging - {$actionName}",
            'review' => "Document review and evaluation process - {$actionName}",
            'approve' => "Document approval and authorization - {$actionName}",
            'process' => "Document processing and handling - {$actionName}",
            'forward' => "Forward document to next office per flowchart - {$actionName}",
            'return' => "Return document to originating office - {$actionName}",
            default => "Perform action: {$actionName}",
        };
    }
}