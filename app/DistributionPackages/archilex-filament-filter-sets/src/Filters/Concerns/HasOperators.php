<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Closure;

trait HasOperators
{
    protected string | Closure | null $defaultOperator = null;

    protected array | Closure $excludedOperators = [];

    protected array | Closure $includedOperators = [];

    public function defaultOperator(string | Closure | null $operator = null): static
    {
        $this->defaultOperator = $operator;

        return $this;
    }

    public function excludeOperators(array | Closure $operators): static
    {
        $this->excludedOperators = $operators;

        return $this;
    }

    public function includeOperators(array | Closure $operators): static
    {
        $this->includedOperators = $operators;

        return $this;
    }

    public function getDefaultOperator(): ?string
    {
        return $this->evaluate($this->defaultOperator);
    }

    public function getExcludedOperators(): array
    {
        return $this->evaluate($this->excludedOperators);
    }

    public function getIncludedOperators(): array
    {
        return $this->evaluate($this->includedOperators);
    }

    protected function operatorIsExcluded(string $operator): bool
    {
        return in_array($operator, $this->getExcludedOperators());
    }

    protected function operatorIsIncluded(string $operator): bool
    {
        if ($this->operatorIsExcluded($operator)) {
            return false;
        }

        $includedOperators = $this->getIncludedOperators();

        if (empty($includedOperators)) {
            return true;
        }

        return in_array($operator, $includedOperators);
    }

    protected function getOperatorOptions(): array
    {
        $operators = $this->getOperators();

        return collect($operators)
            ->filter(
                fn ($name, $operator) => $this->operatorIsIncluded($operator)
            )
            ->toArray();
    }
}
