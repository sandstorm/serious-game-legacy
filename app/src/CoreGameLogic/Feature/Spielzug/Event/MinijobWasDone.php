<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class MinijobWasDone implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion
{
    public function __construct(
        public PlayerId        $playerId,
        public CardId          $minijobCardId,
        public ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            minijobCardId: CardId::fromString($values['minijobCardId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'minijobCardId' => $this->minijobCardId,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
            return $this->resourceChanges->accumulate(new ResourceChanges(zeitsteineChange: -1));
        }
        return new ResourceChanges();
    }

    // For now we decided to put this in the "Jobs" category
    public function getCategoryId(): CategoryId
    {
        return CategoryId::JOBS ;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    // This is a ZeitsteinAktion that does not use a Zeitsteinslot -> return 0
    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 0;
    }
}
