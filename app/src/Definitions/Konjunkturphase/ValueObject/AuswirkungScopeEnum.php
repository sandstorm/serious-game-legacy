<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\ValueObject;

enum AuswirkungScopeEnum: string
{
    case LOANS_INTEREST_RATE = 'Kreditzins';
    case STOCKS_BONUS = 'Aktien Kursbonus';
    case CRYPTO = 'Crypto Kursbonus';
    case DIVIDEND = 'Dividende';
    case REAL_ESTATE = 'Immobilien';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'Kreditzins' => self::LOANS_INTEREST_RATE,
            'Aktien Kursbonus' => self::STOCKS_BONUS,
            'Crypto Kursbonus' => self::CRYPTO,
            'Dividende' => self::DIVIDEND,
            'Immobilien' => self::REAL_ESTATE,
            default => throw new \InvalidArgumentException("Invalid value: $value"),
        };
    }
}
