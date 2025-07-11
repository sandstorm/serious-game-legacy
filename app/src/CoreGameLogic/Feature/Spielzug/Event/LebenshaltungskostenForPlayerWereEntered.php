<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesPlayerInput;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\UpdatesInputForLebenshaltungskosten;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class LebenshaltungskostenForPlayerWereEntered implements GameEventInterface, ProvidesPlayerInput, UpdatesInputForLebenshaltungskosten
{
    public function __construct(
        public PlayerId     $playerId,
        private MoneyAmount $playerInput,
        private MoneyAmount $expectedInput,
        private bool        $wasInputCorrect
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            playerInput: new MoneyAmount($values['playerInput']),
            expectedInput: new MoneyAmount($values['expectedInput']),
            wasInputCorrect: $values['wasInputCorrect'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'playerInput' => $this->playerInput,
            'expectedInput' => $this->expectedInput,
            'wasInputCorrect' => $this->wasInputCorrect,
        ];
    }

    public function getPlayerInput(): MoneyAmount
    {
        return $this->playerInput;
    }

    public function getExpectedInput(): MoneyAmount
    {
        return $this->expectedInput;
    }

    public function wasInputCorrect(): bool
    {
        return $this->wasInputCorrect;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getUpdatedValue(): MoneyAmount
    {
        return $this->playerInput;
    }
}
