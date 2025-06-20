<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour\ProvidesPlayerInput;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour\UpdatesInputForSteuernUndAbgaben;
use Domain\CoreGameLogic\PlayerId;

final readonly class SteuernUndAbgabenForPlayerWereEntered implements GameEventInterface, ProvidesPlayerInput, UpdatesInputForSteuernUndAbgaben
{
    public function __construct(
        public PlayerId $playerId,
        private float     $playerInput,
        private float     $expectedInput,
        private bool    $wasInputCorrect
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            playerInput: $values['playerInput'],
            expectedInput: $values['expectedInput'],
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

    public function getPlayerInput(): float
    {
        return $this->playerInput;
    }

    public function getExpectedInput(): float
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

    public function getUpdatedValue(): float
    {
        return $this->playerInput;
    }
}
