<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;

final readonly class GuthabenInitialized implements ProvidesModifiers, GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public Guthaben $initialGuthaben,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            initialGuthaben: new Guthaben($values['guthaben']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'guthaben' => $this->initialGuthaben,
        ];
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($this->playerId->equals($playerId)) {
            return new ModifierCollection([new GuthabenChange($this->initialGuthaben->value)]);
        }
        return new ModifierCollection([]);
    }
}
