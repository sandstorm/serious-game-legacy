<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

readonly final class InvestmentPrice implements \JsonSerializable
{
    public function __construct(public InvestmentId $investmentId, public MoneyAmount $price)
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            investmentId: InvestmentId::from($values['investmentId']),
            price: new MoneyAmount($values['price'])
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'investmentId' => $this->investmentId->value,
            'price' => $this->price->value
        ];
    }
}
