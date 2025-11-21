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
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class CardWasSkipped implements ZeitsteinAktion, DrawsCard, GameEventInterface, ProvidesResourceChanges, Loggable
{
    public function __construct(
        public PlayerId   $playerId,
        public CardId     $cardId,
        public PileId     $pileId,
        public ResourceChanges $resourceChanges,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            cardId: new CardId($values['cardId']),
            pileId: PileId::fromArray($values['pileId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'cardId' => $this->cardId,
            'pileId' => $this->pileId,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getCategoryId(): CategoryId
    {
        return $this->pileId->categoryId;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 1;
    }

    public function getPileId(): PileId
    {
        return $this->pileId;
    }

    public function getLogEntry(): LogEntry
    {
        /** @var KategorieCardDefinition $cardDefinition */
        $cardDefinition = CardFinder::getInstance()->getCardById($this->cardId);
        return new LogEntry(
            text: "skippt Karte '" . $cardDefinition->getTitle() . "'",
            playerId: $this->playerId,
            resourceChanges: $this->resourceChanges,
        );
    }
}
