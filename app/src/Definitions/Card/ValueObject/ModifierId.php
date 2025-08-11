<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum ModifierId: string
{
    case AUSSETZEN = 'Aussetzen';
    case BIND_ZEITSTEIN_FOR_JOB = 'Bind Zeitstein';
    case GEHALT_CHANGE = 'Gehaltsänderung';
    case LEBENSHALTUNGSKOSTEN_MULTIPLIER = 'Lebenshaltungskosten Multiplikator';
    case LEBENSHALTUNGSKOSTEN_MIN_VALUE = 'Lebenshaltungskosten mindestwert';
    case BERUFSUNFAEHIGKEITSVERSICHERUNG = 'Berufsunfähigkeitsversicherung';
    case HAFTPFLICHTVERSICHERUNG = 'Haftpflichtversicherung';
    case INVESTITIONSSPERRE = 'Investitionssperre';
    case JOBVERLUST = 'Jobverlust';
    case PRIVATE_UNFALLVERSICHERUNG = 'private Unfallversicherung';
    case EMPTY = 'Leerer Modifier (TODO remove)';
}
