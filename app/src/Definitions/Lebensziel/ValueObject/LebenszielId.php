<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\ValueObject;

class LebenszielId implements \JsonSerializable
{
    /**
     * @var array<string,self>
     */
    private static array $instances = [];

    private static function instance(int $value): self
    {
        return self::$instances[$value] ??= new self($value);
    }

    public static function create(int $value): self
    {
        return self::instance($value);
    }

    private function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[LebenszielId: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
