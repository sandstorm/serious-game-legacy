<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class ImmobilieWasBoughtForPlayer implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion, DrawsCard
{
    /**
     * @param PlayerId $playerId
     * @param CardId $cardId
     * @param PileId $pileId
     * @param ResourceChanges $resourceChanges
     */
    public function __construct(
        public PlayerId        $playerId,
        public CardId          $cardId,
        public PileId          $pileId,
        public ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            cardId: CardId::fromString($values['cardId']),
            pileId: PileId::fromArray($values['pileId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'cardId' => $this->cardId->value,
            'pileId' => $this->pileId,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getCategoryId(): CategoryId
    {
        return CategoryId::INVESTITIONEN;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 1; // Buying immoblie uses one Zeitsteinslot
    }

    public function getPileId(): PileId
    {
        return $this->pileId;
    }
}
