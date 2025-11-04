<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class WorkflowStepperField extends Field
{
    protected string $view = 'forms.components.workflow-stepper-field';

    protected array|\Closure $actions = [];

    public function getSelectedActions(): array
    {
        return $this->getState() ?? [];
    }

    public function getActions()
    {
        return $this->evaluate($this->actions);
    }

    public function actions(\Closure|array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }
}
