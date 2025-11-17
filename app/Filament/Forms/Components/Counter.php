<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class Counter extends Field
{
    protected string $view = 'filament.forms.components.counter';

    protected int|string|null $minValue = 1;

    protected int|string|null $maxValue = 1000;

    protected int|string|null $step = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(1);
    }

    public function minValue(int|string|null $value): static
    {
        $this->minValue = max(1, $value ?? 1);

        return $this;
    }

    public function getMinValue(): int|string|null
    {
        return max(1, $this->minValue ?? 1);
    }

    public function maxValue(int|string|null $value): static
    {
        $this->maxValue = $value;

        return $this;
    }

    public function getMaxValue(): int|string|null
    {
        return $this->maxValue;
    }

    public function step(int|string|null $value): static
    {
        $this->step = $value;

        return $this;
    }

    public function getStep(): int|string|null
    {
        return $this->step;
    }

    public function isDisabled(): bool
    {
        return $this->evaluate($this->isDisabled) ?? false;
    }
}
