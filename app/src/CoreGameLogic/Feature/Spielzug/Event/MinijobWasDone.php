<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class MinijobWasDone implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion, DrawsCard, Loggable
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

    public function getPileId(): PileId
    {
        return new PileId(CategoryId::MINIJOBS, LebenszielPhaseId::ANY_PHASE);
    }

    public function getLogEntry(): LogEntry
    {
        $cardDefinition = CardFinder::getInstance()->getCardById($this->minijobCardId, MinijobCardDefinition::class);
        return new LogEntry(
            playerId: $this->playerId,
            text: "macht Minijob '" . $cardDefinition->getTitle() . "'",
            resourceChanges: $this->resourceChanges,
        );
    }
}
