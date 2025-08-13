<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

enum StockType: string
{
    case LOW_RISK = 'low risk';
    case HIGH_RISK = 'high risk';

    public function toPrettyString(): string
    {
        return match ($this) {
            self::LOW_RISK => 'Low Risk',
            self::HIGH_RISK => 'High Risk',
        };
    }

}
