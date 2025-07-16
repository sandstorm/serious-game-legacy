<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\Modifier;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ModifierId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class JobOfferWasAccepted implements GameEventInterface, ProvidesModifiers
{
    public function __construct(
        public PlayerId    $playerId,
        public CardId      $cardId,
        public MoneyAmount $gehalt,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            cardId: CardId::fromString($values['cardId']),
            gehalt: new MoneyAmount($values['gehalt'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'cardId' => $this->cardId,
            'gehalt' => $this->gehalt,
        ];
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($this->playerId !== $playerId) {
            return new ModifierCollection([]);
        }

        return new ModifierCollection([new Modifier(ModifierId::BIND_ZEITSTEIN)]);
    }

}
