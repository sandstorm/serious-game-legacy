<?php

declare(strict_types=1);

namespace Domain\Definitions\Job\ValueObject;

final readonly class JobId implements \JsonSerializable
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[JobId: '.$this->value.']';
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function equals(JobId $other): bool
    {
        return $this->value === $other->value;
    }
}
