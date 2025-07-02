<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class PlayerHasCompletedMoneysheetForCurrentKonjunkturphase implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId $playerId,
        public CurrentYear $year,
        public MoneyAmount $guthabenChange,
    ) {
    }

    /**
     * @param array<string, mixed> $values
     * @return GameEventInterface
     */
    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            year: new CurrentYear($values['year']),
            guthabenChange: new MoneyAmount($values['guthabenChange']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            "year" => $this->year,
            "playerId" => $this->playerId,
            "guthabenChange" => $this->guthabenChange,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return new ResourceChanges(
                guthabenChange: $this->guthabenChange
            );
        }
        return new ResourceChanges();
    }
}
