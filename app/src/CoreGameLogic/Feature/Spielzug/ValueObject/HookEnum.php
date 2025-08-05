<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

// TODO remove this and use modifierId instead
enum HookEnum: string
{
    case GEHALT = 'Gehalt';
    case ZEITSTEINE = 'Zeitsteine';
    case AUSSETZEN = 'Aussetzen';
    case LEBENSHALTUNGSKOSTEN_MULTIPLIER = 'Lebenshaltungskosten Multiplikator';
    case LEBENSHALTUNGSKOSTEN_MIN_VALUE = 'Lebenshaltungskosten Mindestwert';
}
