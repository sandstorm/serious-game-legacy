<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

enum StockType: string
{
    case LOW_RISK = 'low risk';
    case HIGH_RISK = 'high risk';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'low risk' => self::LOW_RISK,
            'high risk' => self::HIGH_RISK,
            default => throw new \InvalidArgumentException('Invalid StockType: ' . $value),
        };
    }

}
