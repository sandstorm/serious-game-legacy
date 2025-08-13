<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use JsonSerializable;

final readonly class InvestmentAmountChanges implements JsonSerializable
{
    /**
     * @param int $amountChange
     */
    public function __construct(
        public int $amountChange = 0,
    )
    {
    }

    /**
     * @param array{
     *     amountChange: int,
     * } $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            amountChange: $values['amountChange'],
        );
    }

    public function __toString(): string
    {
        return '[amountChange: ' . $this->amountChange . ']';
    }

    public function accumulate(self $change): self
    {
        return new self(
            amountChange: $this->amountChange + $change->amountChange,
        );
    }

    /**
     * @return array<int|MoneyAmount>
     */
    public function jsonSerialize(): array
    {
        return [
            'amountChange' => $this->amountChange,
        ];
    }
}
