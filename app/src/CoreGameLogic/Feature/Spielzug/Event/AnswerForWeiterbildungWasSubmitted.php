<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class AnswerForWeiterbildungWasSubmitted implements GameEventInterface, ProvidesResourceChanges, Loggable
{
    public function __construct(
        public PlayerId        $playerId,
        public CardId          $cardId,
        public AnswerId        $selectedAnswerId,
        public bool            $wasCorrect,
    ) {}

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            cardId: CardId::fromString($values['cardId']),
            selectedAnswerId: AnswerId::fromString($values['selectedAnswerId']),
            wasCorrect: $values['wasCorrect'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'cardId' => $this->cardId,
            'selectedAnswerId' => $this->selectedAnswerId,
            'wasCorrect' => $this->wasCorrect,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId) && $this->wasCorrect) {
            return new ResourceChanges(bildungKompetenzsteinChange: +0.5);
        }
        return new ResourceChanges();
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            text: "hat die Weiterbildung " . ($this->wasCorrect ? "richtig" : "falsch") . " beantwortet",
            playerId: $this->playerId,
            resourceChanges: $this->getResourceChanges($this->playerId),
        );
    }
}
