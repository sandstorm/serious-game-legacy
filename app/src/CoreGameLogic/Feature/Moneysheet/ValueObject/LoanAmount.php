<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\ValueObject;

readonly class LoanAmount implements \JsonSerializable
{
    public float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public static function fromString(mixed $input): self
    {
        return new self(floatval($input));
    }

    public function __toString(): string
    {
        return '[LoanAmount: '.$this->value.']';
    }

    public function jsonSerialize(): float
    {
        return $this->value;
    }
}
