<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

enum ModifierId: string
{
    case AUSSETZEN = 'Aussetzen';
    case BIND_ZEITSTEIN = 'Bind Zeitstein';
}
