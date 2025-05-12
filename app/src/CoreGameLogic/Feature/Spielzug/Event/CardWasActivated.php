<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;

final readonly class CardWasActivated implements ProvidesModifiers, GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public CardId $card,
    ) {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($this->card->value === "neues Hobby" && $this->player->equals($playerId)) {
            return new ModifierCollection([new GuthabenChange(-500)]);
        }
        return new ModifierCollection([]);
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            card: new CardId($values['card']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'card' => $this->card,
        ];
    }
}
