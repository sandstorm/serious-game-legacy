<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

use Domain\Definitions\Card\ValueObject\CardId;

/**
 * WHY:
 * ImmobilienCards don't get removed from the game when a player buys them. That means that a player could buy
 * the same ImmobilieCard multiple times, so we cannot just use the CardId as an Id for Immobilien.
 * With a combination of CardId and PlayerTurn we can differentiate between each Immobilie the player has, even if
 * the player got the same ImmobilieCard more than once.
 */
readonly class ImmobilieId implements \JsonSerializable
{
    public function __construct(
        public CardId $cardId,
        public PlayerTurn $playerTurn)
    {
    }

    /**
     * @param array<string, mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            cardId: CardId::fromString($values['cardId']),
            playerTurn: new PlayerTurn($values['playerTurn']),
        );
    }

    public function __toString(): string
    {
        return $this->cardId->value . '_' . $this->playerTurn->value;
    }

    public static function fromString(string $input): self
    {
        [$carIdString, $playerTurnString] = explode('_', $input);
        return new self(CardId::fromString($carIdString), new PlayerTurn(intval($playerTurnString)));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'cardId' => $this->cardId,
            'playerTurn' => $this->playerTurn,
        ];
    }

    public function equals(ImmobilieId $other): bool
    {
        return $this->cardId->equals($other->cardId) && $this->playerTurn->value === $other->playerTurn->value;
    }
}
