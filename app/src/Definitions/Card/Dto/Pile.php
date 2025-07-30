<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class Pile implements \JsonSerializable
{
    /**
     * @param PileId $pileId
     * @param CardId[] $cardIds
     */
    public function __construct(
        protected PileId $pileId,
        protected array $cardIds = [],
    )
    {
    }

    /**
     * @param array<string,mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            pileId: PileId::fromArray($values['pileId']),
            cardIds: array_map(fn ($value) => CardId::fromString($value), $values['cardIds']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'pileId' => $this->pileId,
            'cardIds' => $this->cardIds,
        ];
    }

    /**
     * @return CardId[]
     */
    public function getCardIds(): array
    {
        return $this->cardIds;
    }

    /**
     * @return PileId
     */
    public function getPileId(): PileId
    {
        return $this->pileId;
    }
}
