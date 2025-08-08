<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class ImmobilienPrice implements \JsonSerializable
{
    public function __construct(public CardId $cardId, public MoneyAmount $price)
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            cardId: CardId::fromString($values['cardId']),
            price: new MoneyAmount($values['price'])
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'cardId' => $this->cardId->value,
            'price' => $this->price->value
        ];
    }
}
