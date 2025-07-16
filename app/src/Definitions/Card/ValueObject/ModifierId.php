<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum ModifierId: string
{
    case AUSSETZEN = 'Aussetzen';
    case BIND_ZEITSTEIN_FOR_JOB = 'Bind Zeitstein';
    case GEHALT_CHANGE = 'Gehaltsänderung';
}
