<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour\UpdatesInputForSteuernUndAbgaben;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class SteuernUndAbgabenForPlayerWereCorrected implements GameEventInterface, ProvidesResourceChanges, UpdatesInputForSteuernUndAbgaben
{
    public function __construct(
        public PlayerId     $playerId,
        private MoneyAmount $correctValue,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            correctValue: new MoneyAmount($values['correctValue']),
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
            return new ResourceChanges(guthabenChange: new MoneyAmount(-250));
        }
        return new ResourceChanges();
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getUpdatedValue(): MoneyAmount
    {
        return $this->correctValue;
    }
}
