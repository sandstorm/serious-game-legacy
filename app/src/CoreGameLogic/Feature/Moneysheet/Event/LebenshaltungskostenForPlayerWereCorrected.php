<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour\UpdatesInputForLebenshaltungskosten;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class LebenshaltungskostenForPlayerWereCorrected implements GameEventInterface, ProvidesResourceChanges, UpdatesInputForLebenshaltungskosten
{
    public function __construct(
        public PlayerId $playerId,
        private float     $correctValue,
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
            return new ResourceChanges(guthabenChange: self::getFineForPlayer() * -1);
        }
        return new ResourceChanges();
    }

    public static function getFineForPlayer(): float
    {
        return 250.0; // The fine for the player who made a mistake in entering their living costs
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getUpdatedValue(): float
    {
        return $this->correctValue;
    }
}
