<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum ImmobilienType: string
{
    case WOHNUNG = 'Wohnung';
    case HAUS = 'Haus';
}
