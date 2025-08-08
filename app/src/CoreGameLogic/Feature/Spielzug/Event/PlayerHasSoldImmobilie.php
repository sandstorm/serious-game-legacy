<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class PlayerHasSoldImmobilie implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion, Loggable
{
    /**
     * @param PlayerId $playerId
     * @param ImmobilieId $immobilieId
     * @param CardId $cardId
     * @param ResourceChanges $resourceChanges
     */
    public function __construct(
        protected PlayerId        $playerId,
        protected ImmobilieId     $immobilieId,
        protected CardId          $cardId,
        protected ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            immobilieId: ImmobilieId::fromArray($values['immobilieId']),
            cardId: CardId::fromString($values['cardId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'immobilieId' => $this->immobilieId,
            'cardId' => $this->cardId->value,
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
        return 1; // Selling immobilie uses one Zeitsteinslot
    }

    public function getCardId(): CardId
    {
        return $this->cardId;
    }

    public function getImmobilieId(): ImmobilieId
    {
        return $this->immobilieId;
    }

    public function getLogEntry(): LogEntry
    {
        $kaufpreis = CardFinder::getInstance()->getCardById($this->cardId, ImmobilienCardDefinition::class)->getPurchasePrice();
        return new LogEntry(
            playerId: $this->playerId,
            text: "Verkauft Immobilie (Kaufpreis: {$kaufpreis->formatWithoutHtml()})",
            resourceChanges: $this->resourceChanges,
        );
    }
}
