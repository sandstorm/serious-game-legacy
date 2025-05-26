<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

/**
 * We use this class to keep track of which card is in which pile and in what order.
 */
readonly final class CardOrder implements \JsonSerializable
{
    /**
     * @param PileId $pileId
     * @param CardId[] $cards
     */
    public function __construct(public PileId $pileId, public array $cards)
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            pileId: PileId::from($values['pileId']),
            cards: array_map(fn ($card) => new CardId($card), $values['cards']),
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'pileId' => $this->pileId->value,
            'cards' => array_map(fn (CardId $card) => $card->jsonSerialize(), $this->cards),
        ];
    }
}
