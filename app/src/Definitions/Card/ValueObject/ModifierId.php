<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum ModifierId: string
{
    case AUSSETZEN = 'Aussetzen';
    case BIND_ZEITSTEIN_FOR_JOB = 'Bind Zeitstein';
    case GEHALT_CHANGE = 'Gehaltsänderung';
    case LEBENSHALTUNGSKOSTEN_KIND_INCREASE = 'Lebenshaltungskosten Steigerung durch Kind';
    case LEBENSHALTUNGSKOSTEN_MIN_VALUE = 'Lebenshaltungskosten mindestwert';
    case BERUFSUNFAEHIGKEITSVERSICHERUNG = 'Berufsunfähigkeitsversicherung';
    case HAFTPFLICHTVERSICHERUNG = 'Haftpflichtversicherung';
    case INVESTITIONSSPERRE = 'Investitionssperre';
    case JOBVERLUST = 'Jobverlust';
    case PRIVATE_UNFALLVERSICHERUNG = 'private Unfallversicherung';
    case EMPTY = 'Leerer Modifier (TODO remove)';
    case BERUFSUNFAEHIGKEIT_JOBSPERRE = 'Berufsunfähigkeit Jobsperre';
    case BERUFSUNFAEHIGKEIT_GEHALT = 'Berufsunfähigkeit Gehaltsfortzahlung';
    case BILDUNG_UND_KARRIERE_COST = 'Kosten für Bildung und Karriere';
    case SOZIALES_UND_FREIZEIT_COST = 'Kosten für Soziales und Freizeit';
    case LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER = 'Lebenshaltungskosten Konjunkturphase';
    case KREDITSPERRE = 'Kreditsperre';
    case INCREASED_CHANCE_FOR_REZESSION = 'Erhöhte Chance für eine Rezession';
    case FOR_TESTING_ONLY_NEVER_TRIGGER_EREIGNIS = 'Nur zum Testen: Es werden keine Ereignisse ausgelöst';
    case FOR_TESTING_ONLY_ALWAYS_TRIGGER_EREIGNIS = 'Nur zum Testen: Es werden immer Ereignisse ausgelöst';
}
