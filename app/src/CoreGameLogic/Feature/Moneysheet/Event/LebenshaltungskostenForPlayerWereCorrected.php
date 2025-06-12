<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class LebenshaltungskostenForPlayerWereCorrected implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId $playerId,
        private int     $correctValue,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            correctValue: $values['correctValue'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'correctValue' => $this->correctValue,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId === $this->playerId) {
            return new ResourceChanges(guthabenChange: -250);
        }
        return new ResourceChanges();
    }

}
