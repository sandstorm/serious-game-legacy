<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\BindZeitsteinForJobModifier;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class JobOfferWasAccepted implements GameEventInterface, ProvidesModifiers
{
    public function __construct(
        public PlayerId    $playerId,
        public CardId      $cardId,
        public MoneyAmount $gehalt,
        public PlayerTurn  $playerTurn,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            cardId: CardId::fromString($values['cardId']),
            gehalt: new MoneyAmount($values['gehalt']),
            playerTurn: new PlayerTurn($values['playerTurn']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'cardId' => $this->cardId,
            'gehalt' => $this->gehalt,
            'playerTurn' => $this->playerTurn,
        ];
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if (!$this->playerId->equals($playerId)){
            return new ModifierCollection([]);
        }

        $cardDefinition = CardFinder::getInstance()->getCardById($this->cardId, JobCardDefinition::class);
        return new ModifierCollection([new BindZeitsteinForJobModifier($this->playerId, $this->playerTurn, $cardDefinition->description())]);
    }

}
