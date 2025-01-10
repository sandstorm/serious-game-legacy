<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

use Closure;
use Filament\Support as Support;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Model;

trait CanFormatState
{
    use Support\Concerns\HasAlignment;

    protected bool|Closure $isBadge = false;

    /**
     * @var string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null
     */
    protected string|array|Closure|null $badgeColor = null;

    protected string|Closure|null $badgeIcon = null;

    protected IconPosition|string|Closure|null $badgeIconPosition = null;

    protected bool|Closure $isBulleted = false;

    protected bool|Closure $isListWithLineBreaks = false;

    protected int|Closure|null $listLimit = null;

    protected bool|Closure $isLimitedListExpandable = false;

    protected ?Closure $recordUrl = null;

    public function badge(bool|Closure $condition = true): static
    {
        $this->isBadge = $condition;

        return $this;
    }

    /**
     * @param  string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | Closure | null  $color
     */
    public function badgeColor(string|array|Closure|null $color): static
    {
        $this->badgeColor = $color;

        return $this;
    }

    public function badgeIcon(string|Closure|null $icon): static
    {
        $this->badgeIcon = $icon;

        return $this;
    }

    public function badgeIconPosition(IconPosition|string|Closure|null $position): static
    {
        $this->badgeIconPosition = $position;

        return $this;
    }

    public function bulleted(bool|Closure $condition = true): static
    {
        $this->isBulleted = $condition;

        return $this;
    }

    public function listWithLineBreaks(bool|Closure $condition = true): static
    {
        $this->isListWithLineBreaks = $condition;

        return $this;
    }

    public function limitList(int|Closure|null $limit = 3): static
    {
        $this->listLimit = $limit;

        return $this;
    }

    public function expandableLimitedList(bool|Closure $condition = true): static
    {
        $this->isLimitedListExpandable = $condition;

        return $this;
    }

    public function recordUrl(Closure $recordUrl): static
    {
        $this->recordUrl = $recordUrl;

        return $this;
    }

    public function isBadge(): bool
    {
        return (bool) $this->evaluate($this->isBadge);
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    public function getBadgeColor(Model $state): string|array|null
    {
        return $this->evaluate(
            $this->badgeColor,
            namedInjections: [
                'record' => $state,
                'state' => $state,
            ],
            typedInjections: [
                Model::class => $state,
                $state::class => $state,
            ]
        );
    }

    public function getBadgeIcon(Model $state): ?string
    {
        return $this->evaluate(
            $this->badgeIcon,
            namedInjections: [
                'record' => $state,
                'state' => $state,
            ],
            typedInjections: [
                Model::class => $state,
                $state::class => $state,
            ]
        );
    }

    public function getBadgeIconPosition(Model $state): IconPosition|string|null
    {
        return $this->evaluate(
            $this->badgeIconPosition,
            namedInjections: [
                'record' => $state,
                'state' => $state,
            ],
            typedInjections: [
                Model::class => $state,
                $state::class => $state,
            ]
        );
    }

    public function isBulleted(): bool
    {
        return (bool) $this->evaluate($this->isBulleted);
    }

    public function isListWithLineBreaks(): bool
    {
        return $this->evaluate($this->isListWithLineBreaks) || $this->isBulleted();
    }

    public function getListLimit(): ?int
    {
        return $this->evaluate($this->listLimit);
    }

    public function isLimitedListExpandable(): bool
    {
        return (bool) $this->evaluate($this->isLimitedListExpandable);
    }

    public function formatState(Model $state): mixed
    {
        $formattedState = $this->getRecordLabelFromRecord($state);

        if (blank($formattedState) && $state instanceof Support\Contracts\HasLabel) {
            $formattedState = $state->getLabel();
        }

        if (blank($formattedState) && $state instanceof \Filament\Models\Contracts\HasName) {
            $formattedState = $state->getFilamentName();
        }

        if (
            blank($formattedState)
            && collect($attributes = $state->getAttributes())->hasAny(['title', 'name', 'full_name'])
        ) {
            // Enables support for `spatie/laravel-translatable`.
            $formattedState = $state->getAttributeValue('title') ?? $state->getAttributeValue('name') ?? $state->getAttributeValue('full_name');
        }

        $formattedState ??= $state->getKey();

        return $formattedState;
    }

    public function getRecordUrl(Model $record): ?string
    {
        if ($this->recordUrl) {
            return $this->evaluate(
                $this->recordUrl,
                namedInjections: [
                    'record' => $record,
                    'state' => $record,
                ],
                typedInjections: [
                    Model::class => $record,
                    $record::class => $record,
                ]
            );
        }

        return null;
    }
}
