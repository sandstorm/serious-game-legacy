<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Archilex\AdvancedTables\Enums\Status;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasStatus
{
    use EvaluatesClosures;

    protected string | Status | Closure $minimumStatusForDisplay = Status::Pending;

    protected string | Status | Closure $initialStatus = Status::Pending;

    public function minimumStatusForDisplay(string | Status | Closure $status): static
    {
        $this->minimumStatusForDisplay = $status;

        return $this;
    }

    public function initialStatus(string | Status | Closure $status): static
    {
        $this->initialStatus = $status;

        return $this;
    }

    public function getMinimumStatusForDisplay(): string | Status
    {
        return $this->evaluate($this->minimumStatusForDisplay);
    }

    public function getInitialStatus(): string | Status
    {
        return $this->evaluate($this->initialStatus);
    }
}
