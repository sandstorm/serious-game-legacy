<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\ValueObjectInterface;

class PlayerId implements \JsonSerializable, ValueObjectInterface
{
    /**
     * @var array<string,self>
     */
    private static array $instances = [];

    private static function instance(string $value): self
    {
        return self::$instances[$value] ??= new self($value);
    }

    public static function fromString(string $value): self
    {
        return self::instance($value);
    }

    private function __construct(public readonly string $value)
    {
    }

    public function __toString(): string
    {
        return '[Player: '.$this->value.']';
    }

    public function equals(PlayerId $other): bool
    {
        return $this->value === $other->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
