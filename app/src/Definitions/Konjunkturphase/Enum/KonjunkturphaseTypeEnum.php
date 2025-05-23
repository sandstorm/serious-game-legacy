<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Enum;

enum KonjunkturphaseTypeEnum: string
{
    case AUFSCHWUNG = 'Aufschwung';
    case REZESSION = 'Rezession';
    case BOOM = 'Boom';
    case DEPRESSION = 'Depression';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'Aufschwung' => self::AUFSCHWUNG,
            'Rezession' => self::REZESSION,
            'Boom' => self::BOOM,
            'Depression' => self::DEPRESSION,
            default => throw new \InvalidArgumentException('Invalid KonjunkturphaseType: '.$value),
        };
    }
}
