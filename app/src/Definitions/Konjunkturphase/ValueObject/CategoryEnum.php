<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\ValueObject;

enum CategoryEnum: string
{
    case BILDUNG = 'Bildung & Karriere';
    case FREIZEIT = 'Freizeit & Sozial';
    case INVESTITIONEN = 'Investitionen';
    case ERWEBSEINKOMMEN = 'Erwerbseinkommen';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'Bildung & Karriere' => self::BILDUNG,
            'Freizeit & Sozial' => self::FREIZEIT,
            'Investitionen' => self::INVESTITIONEN,
            'Erwerbseinkommen' => self::ERWEBSEINKOMMEN,
            default => throw new \InvalidArgumentException("Invalid Category: $value"),
        };
    }
}
