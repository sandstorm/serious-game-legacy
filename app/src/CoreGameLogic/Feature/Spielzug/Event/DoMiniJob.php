<?php

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class DoMiniJob implements GameEventInterface
{
    /**
     * @param CardId[] $minijobs
     */
    public function __construct(
        public PlayerId $playerId,
        public CardId $cardId,
        public array    $minijobs,
        public MoneyAmount $moneyAmount,
    )
    {

    }

    public static function fromArray(array $values): GameEventInterface
    {
        // TODO: Implement fromArray() method.
        return[];
    }

    public function jsonSerialize(): array
    {
        // TODO: Implement jsonSerialize() method.
        return[];
    }
}
