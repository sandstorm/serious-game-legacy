<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Pile\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

final readonly class CardWasActivated implements ProvidesModifiers, ProvidesResourceChanges, DrawsCard, GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public PileId $pileId,
        public CardId $cardId,
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
            pileId: PileId::fromString($values['pileId']),
            cardId: new CardId($values['cardId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'pileId' => $this->pileId,
            'cardId' => $this->cardId->jsonSerialize(),
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getPileId(): PileId
    {
        return $this->pileId;
    }
}
