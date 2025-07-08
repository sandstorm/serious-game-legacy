<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic;

use Ramsey\Uuid\Uuid;

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

    public static function unique(): self
    {
        $uuid = Uuid::uuid4();
        return new self($uuid->toString());
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
