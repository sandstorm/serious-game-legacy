<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class CardWasSkipped implements DrawsCard, GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId $player,
        public CardId   $card,
        public PileId   $pile,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            card: new CardId($values['card']),
            pile: PileId::from($values['pile']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'card' => $this->card,
            'pile' => $this->pile,
        ];
    }

    public function getPileId(): PileId
    {
        return $this->pile;
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->player)) {
            // Skipping will always consume 1 Zeitstein
            return new ResourceChanges(zeitsteineChange: -1);
        }
        return new ResourceChanges();
    }
}
