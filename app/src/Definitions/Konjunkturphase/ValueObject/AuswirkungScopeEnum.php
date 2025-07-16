<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\ValueObject;

enum AuswirkungScopeEnum: string
{
    case ZEITSTEINE = 'Zeitsteine';
    case LEBENSERHALTUNGSKOSTEN = 'Lebenshaltungskosten';
    case BILDUNG = 'Bildung & Karriere';
    case FREIZEIT = 'Freizeit & Sozial';
    case INVESTITIONEN = 'Investitionen';
    case ERWEBSEINKOMMEN = 'Erwerbseinkommen';
    case LOANS_INTEREST_RATE = 'Kreditzins';
    case STOCKS_BONUS = 'Aktien Kursbonus';
    case DIVIDEND = 'Dividende';
    case REAL_ESTATE = 'Immobilien';
    case CRYPTO = 'Crypto Kursbonus';
    case BONUS_INCOME = 'Bonuseinkommen';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'Zeitsteine' => self::ZEITSTEINE,
            'Lebenshaltungskosten' => self::LEBENSERHALTUNGSKOSTEN,
            'Bildung & Karriere' => self::BILDUNG,
            'Freizeit & Sozial' => self::FREIZEIT,
            'Investitionen' => self::INVESTITIONEN,
            'Erwerbseinkommen' => self::ERWEBSEINKOMMEN,
            'Kreditzins' => self::LOANS_INTEREST_RATE,
            'Aktien Kursbonus' => self::STOCKS_BONUS,
            'Dividende' => self::DIVIDEND,
            'Immobilien' => self::REAL_ESTATE,
            'Crypto Kursbonus' => self::CRYPTO,
            'Bonuseinkommen' => self::BONUS_INCOME,
            default => throw new \InvalidArgumentException("Invalid value: $value"),
        };
    }
}
