<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\ValueObject;

readonly class LoanId implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[LoanId: ' . $this->value . ']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function equals(LoanId $other): bool
    {
        return $this->value === $other->value;
    }
}
