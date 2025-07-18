<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

use JsonSerializable;

readonly class MoneyAmount implements JsonSerializable
{
    public float $value;

    public function __construct(float $value)
    {
        $this->value = round($value, 2);
    }

    public static function fromString(mixed $input): self
    {
        return new self(floatval($input));
    }

    public function __toString(): string
    {
        return '[MoneyAmount: '.$this->value.']';
    }

    public function jsonSerialize(): float
    {
        return $this->value;
    }

    public function add(MoneyAmount $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(MoneyAmount $other): self
    {
        return new self($this->value - $other->value);
    }

    public function multiply(MoneyAmount $other): self
    {
        return new self($this->value * $other->value);
    }

    public function equals(MoneyAmount|float $other): bool
    {
        $tolerance = 0.001;
        if ($other instanceof MoneyAmount) {
            $otherValue = $other->value;
        } else {
            $otherValue = $other;
        }
        return $otherValue - $tolerance < $this->value && $this->value < $otherValue + $tolerance;
    }

    public function format(): string
    {
        return "<span class='text--currency'>" . number_format($this->value, 2, ',', '.') . ' €' . "</span>";
    }
}
