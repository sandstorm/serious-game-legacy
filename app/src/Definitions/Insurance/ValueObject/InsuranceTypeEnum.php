<?php

declare(strict_types=1);

namespace Domain\Definitions\Insurance\ValueObject;

enum InsuranceTypeEnum: string
{
    case HAFTPFLICHT = 'Haftpflicht';
    case UNFALLVERSICHERUNG = 'Unfallversicherung';
    case BERUFSUNFAEHIGKEITSVERSICHERUNG = 'Berufsunfähigkeitsversicherung';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'Haftpflicht' => self::HAFTPFLICHT,
            'Unfallversicherung' => self::UNFALLVERSICHERUNG,
            'Berufsunfähigkeitsversicherung' => self::BERUFSUNFAEHIGKEITSVERSICHERUNG,
            default => throw new \InvalidArgumentException('Invalid InsuranceType: '.$value),
        };
    }
}
