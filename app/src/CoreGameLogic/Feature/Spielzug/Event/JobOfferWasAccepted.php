<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;

final readonly class JobOfferWasAccepted implements GameEventInterface, ProvidesModifiers
{
    public function __construct(
        public PlayerId $player,
        public CardId $job,
        public Gehalt $gehalt,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            job: CardId::fromString($values['job']),
            gehalt: new Gehalt($values['gehalt'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'job' => $this->job,
            'gehalt' => $this->gehalt,
        ];
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        // TODO: Implement getModifiers() method. -> -1 Zeitstein
        return new ModifierCollection([]);
    }
}
