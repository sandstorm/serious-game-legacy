<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Pile\State\dto;

use Domain\Definitions\Cards\ValueObject\CardId;
use Domain\Definitions\Cards\ValueObject\PileId;

/**
 * We use this class to keep track of which card is in which pile and in what order.
 */
readonly final class Pile
{
    /**
     * @param PileId $pileId
     * @param CardId[] $cards
     */
    public function __construct(public PileId $pileId, public array $cards)
    {
    }

    /**
     * @param array{pileId: string, cards: string[]} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            pileId: PileId::fromString($values['pileId']),
            cards: array_map(fn ($card) => new CardId($card), $values['cards']),
        );
    }
}
