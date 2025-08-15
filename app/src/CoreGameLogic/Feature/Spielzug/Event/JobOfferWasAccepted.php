<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\BindZeitsteinForJobModifier;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class JobOfferWasAccepted implements GameEventInterface, ProvidesModifiers, ProvidesResourceChanges, ZeitsteinAktion, DrawsCard, Loggable
{
    public function __construct(
        public PlayerId    $playerId,
        public CardId      $cardId,
        public MoneyAmount $gehalt,
        public PlayerTurn  $playerTurn,
        public ResourceChanges $resourceChanges,
        public PileId $pileId,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            cardId: CardId::fromString($values['cardId']),
            gehalt: new MoneyAmount($values['gehalt']),
            playerTurn: new PlayerTurn($values['playerTurn']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
            pileId: PileId::fromArray($values['pileId']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'cardId' => $this->cardId,
            'gehalt' => $this->gehalt,
            'playerTurn' => $this->playerTurn,
            'resourceChanges' => $this->resourceChanges,
            'pileId' => $this->pileId,
        ];
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if (!$this->playerId->equals($playerId)){
            return new ModifierCollection([]);
        }

        $cardDefinition = CardFinder::getInstance()->getCardById($this->cardId, JobCardDefinition::class);
        return new ModifierCollection([new BindZeitsteinForJobModifier($this->playerId, $this->playerTurn, $cardDefinition->getDescription())]);
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
        return CategoryId::JOBS;
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
        $cardDefinition = CardFinder::getInstance()->getCardById($this->cardId);
        return new LogEntry(
            playerId: $this->playerId,
            text: "nimmt Job '" . $cardDefinition->getTitle() . "' an",
            resourceChanges: $this->resourceChanges,
        );
    }
}
