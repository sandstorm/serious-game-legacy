<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\ValueObject;

use Ramsey\Uuid\Uuid;

readonly class LoanId implements \JsonSerializable
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[LoanId: ' . $this->value . ']';
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function equals(LoanId $other): bool
    {
        return $this->value === $other->value;
    }

    public static function unique(): self
    {
        $uuid = Uuid::uuid4();
        return new self($uuid->toString());
    }
}
