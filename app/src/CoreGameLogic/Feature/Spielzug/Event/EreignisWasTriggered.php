<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierBuilder;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class EreignisWasTriggered implements ProvidesModifiers, ProvidesResourceChanges, GameEventInterface
{
    /**
     * @var EreignisCardDefinition
     */
    private EreignisCardDefinition $ereignisCardDefinition;

    /**
     * @param PlayerId $playerId
     * @param CardId $ereignisCardId
     * @param PlayerTurn $playerTurn
     * @param Year $year
     * @param ResourceChanges $resourceChanges
     */
    public function __construct(
        public PlayerId $playerId,
        public CardId $ereignisCardId,
        public PlayerTurn $playerTurn,
        public Year $year,
        public ResourceChanges $resourceChanges,
    ) {
        $this->ereignisCardDefinition = CardFinder::getInstance()->getCardById($this->ereignisCardId,
            EreignisCardDefinition::class);
    }

    /**
     * @param PlayerId $playerId
     * @return ModifierCollection
     */
    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($playerId->equals($this->playerId)) {
            $modifiers = [];
            foreach ($this->ereignisCardDefinition->getModifierIds() as $modifierId) {
                $modifiers = [
                    ...$modifiers,
                    ...ModifierBuilder::build(
                        modifierId: $modifierId,
                        playerId: $playerId,
                        playerTurn: $this->playerTurn,
                        year: $this->year,
                        modifierParameters: $this->ereignisCardDefinition->getModifierParameters(),
                        description: $this->ereignisCardDefinition->getDescription(),
                    )
                ];
            }
            return new ModifierCollection($modifiers);
        }
        return new ModifierCollection([]);
    }


    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if (!$playerId->equals($this->playerId)) {
            return new ResourceChanges();
        }
        return $this->resourceChanges;
    }


    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            ereignisCardId: new CardId($values['ereignisCardId']),
            playerTurn: new PlayerTurn($values['playerTurn']),
            year: new Year($values['year']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'ereignisCardId' => $this->ereignisCardId,
            'playerTurn' => $this->playerTurn,
            'year' => $this->year,
            'resourceChanges' => $this->resourceChanges,
        ];
    }
}
