<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

enum HookEnum: string
{
    case GEHALT = 'Gehalt';
    case ZEITSTEINE = 'Zeitsteine';
    case AUSSETZEN = 'Aussetzen';
}
