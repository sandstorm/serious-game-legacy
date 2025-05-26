<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic;

class PlayerId implements \JsonSerializable
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

    public static function random(): self
    {
        // Generate a random 6-character alphanumeric string
        $randomId = substr(bin2hex(random_bytes(4)), 0, 6);
        return self::instance($randomId);
    }

    private function __construct(public readonly string $value)
    {
    }

    public function __toString(): string
    {
        // !!! important to return direct value for routing etc !!!
        return $this->value;
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
