<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Dto;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class ImmobilienPrice implements \JsonSerializable
{
    public function __construct(public ImmobilieId $immobilieId, public MoneyAmount $price)
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            immobilieId: ImmobilieId::fromArray($values['immobilieId']),
            price: new MoneyAmount($values['price'])
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'immobilieId' => $this->immobilieId,
            'price' => $this->price->value
        ];
    }
}
