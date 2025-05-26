<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

readonly class EreignisId implements \JsonSerializable
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[EreignisId: '.$this->value.']';
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
