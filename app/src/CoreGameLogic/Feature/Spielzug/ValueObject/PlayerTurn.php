<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

readonly class PlayerTurn implements \JsonSerializable
{
    /**
     * @param int $value duration in number of turns
     */
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[PlayerTurn: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
