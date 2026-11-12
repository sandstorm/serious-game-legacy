<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\CoreGameLogic\PlayerId;

final readonly class GameReport
{
    /**
     * @param PlayerReport[] $players in player order
     * @param KeyMoment[] $keyMoments in the order they happened
     * @param Highlight[] $highlights
     * @param bool $isFromOlderDefinitionsVersion the game was created with other cards and Konjunkturphasen, so the
     *     yearly moneysheet is not split into its parts and some names may be missing
     */
    public function __construct(
        public ?PlayerId $winnerId,
        public array $players,
        public GuthabenTimeline $timeline,
        public InvestmentPriceHistory $investmentPrices,
        public array $keyMoments,
        public array $highlights,
        public bool $isFromOlderDefinitionsVersion = false,
    ) {
    }

    public function getPlayer(PlayerId $playerId): PlayerReport
    {
        foreach ($this->players as $player) {
            if ($player->playerId->equals($playerId)) {
                return $player;
            }
        }
        throw new \InvalidArgumentException('Player ' . $playerId . ' is not part of the game report', 1794414799);
    }

    public function getWinner(): ?PlayerReport
    {
        return $this->winnerId === null ? null : $this->getPlayer($this->winnerId);
    }

    /**
     * @return array<int, KeyMoment[]> keyed by year
     */
    public function getKeyMomentsByYear(): array
    {
        $byYear = [];
        foreach ($this->keyMoments as $keyMoment) {
            $byYear[$keyMoment->year->value][] = $keyMoment;
        }
        return $byYear;
    }

    public function getYearStart(int $year): ?YearStart
    {
        foreach ($this->timeline->yearStarts as $yearStart) {
            if ($yearStart->year->value === $year) {
                return $yearStart;
            }
        }
        return null;
    }
}
