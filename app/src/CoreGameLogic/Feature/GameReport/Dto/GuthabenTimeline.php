<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\CoreGameLogic\PlayerId;

/**
 * The Guthaben of every player after each Spielzug, plus the Guthaben at the end of the game as last value.
 */
final readonly class GuthabenTimeline
{
    /**
     * @param array<string, list<float>> $valuesByPlayer keyed by player id
     * @param YearStart[] $yearStarts
     */
    public function __construct(
        private array $valuesByPlayer,
        public array $yearStarts,
    ) {
    }

    /**
     * @return list<float>
     */
    public function getValuesForPlayer(PlayerId $playerId): array
    {
        return $this->valuesByPlayer[$playerId->value] ?? [];
    }

    public function getNumberOfValues(): int
    {
        $first = array_values($this->valuesByPlayer)[0] ?? [];
        return count($first);
    }

    public function getMaxValue(): float
    {
        return max(0, ...array_merge(...array_values($this->valuesByPlayer)));
    }

    public function getMinValue(): float
    {
        return min(0, ...array_merge(...array_values($this->valuesByPlayer)));
    }
}
