<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class WorkflowStepperField extends Field
{
    protected string $view = 'forms.components.workflow-stepper-field';
    
    protected array | \Closure $actionTypes = [];
    
    public function getSelectedActions(): array
    {
        return $this->getState() ?? [];
    }
    
    public function getActionTypes()
    {
        return $this->evaluate($this->actionTypes);
    }
    
    public function actionTypes(\Closure | array $actionTypes): static
    {
        $this->actionTypes = $actionTypes;
        
        return $this;
    }
}