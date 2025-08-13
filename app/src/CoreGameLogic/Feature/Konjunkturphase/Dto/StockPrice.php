<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Dto;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class StockPrice implements \JsonSerializable
{
    public function __construct(public StockType $stockType, public MoneyAmount $sharePrice)
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            stockType: StockType::from($values['stockType']),
            sharePrice: new MoneyAmount($values['sharePrice'])
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'stockType' => $this->stockType->value,
            'sharePrice' => $this->sharePrice->value
        ];
    }
}
