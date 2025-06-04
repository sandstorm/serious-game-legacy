<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;

final readonly class CardWasActivated implements ZeitsteinAktion, ProvidesModifiers, ProvidesResourceChanges, DrawsCard, GameEventInterface
{
    public function __construct(
        public PlayerId        $playerId,
        public PileId          $pileId,
        public CardId          $cardId,
        public CategoryEnum    $category,
        public ResourceChanges $resourceChanges
    ) {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        return new ModifierCollection([]);
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            pileId: PileId::from($values['pileId']),
            cardId: new CardId($values['cardId']),
            category: CategoryEnum::from($values['category']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'pileId' => $this->pileId,
            'cardId' => $this->cardId->jsonSerialize(),
            'category' => $this->category->value,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getPileId(): PileId
    {
        return $this->pileId;
    }

    public function getCategory(): CategoryEnum
    {
        return $this->category;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }
}
