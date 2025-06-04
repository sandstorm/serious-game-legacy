<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\ValueObject;

enum PlayerColors: string
{
    case RED = '#dc5d5d';
    case GREEN = '#81de81';
    case BLUE = '#29d';
    case YELLOW = '#f0e85e';

    /**
     * @return string[]
     */
    public static function asArray(): array
    {
        return [
            self::RED->value,
            self::GREEN->value,
            self::BLUE->value,
            self::YELLOW->value,
        ];
    }
}
