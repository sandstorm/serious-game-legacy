<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

enum HookEnum: string
{
    case GEHALT = 'Gehalt';
    case ZEITSTEINE = 'Zeitsteine';
    case AUSSETZEN = 'Aussetzen';
    case LEBENSHALTUNGSKOSTEN_PERCENT_INCREASE = 'Lebenshaltungskostenerhöhung';
    case LEBENSHALTUNGSKOSTEN_MIN_VALUE = 'Lebenshaltungskosten Mindestwert';
    case BERUFSUNFAEHIGKEITSVERSICHERUNG = 'Berufsunfähigkeitsversicherung';
    case HAFTPFLICHTVERSICHERUNG = 'Haftpflichtversicherung';
    case INVESTITIONSSPERRE = 'Investitionssperre';
    case JOBVERLUST = 'Jobverlust';
    case PRIVATE_UNFALLVERSICHERUNG = 'private Unfallversicherung';
    case BERUFSUNFAEHIGKEIT_JOBSPERRE = 'Jobsperre';
    case BERUFSUNFAEHIGKEIT_GEHALT = 'bu gehalt';
    case BILDUNG_UND_KARRIERE_COST = 'Kosten für Bildung und Karriere';
    case SOZIALES_UND_FREIZEIT_COST = 'Kosten für Soziales und Freizeit';
    case IMMOBILIEN_COST = 'Kosten für Immobilien';
    case KREDITSPERRE = 'Kreditsperre';
    case LEBENSHALTUNGSKOSTEN_MULTIPLIER = 'Lebenshaltungskosten Multiplikator';
    case INCREASED_CHANCE_FOR_REZESSION = 'Erhöhte Chance for Rezession als nächste Konjunkturphase';
    case FOR_TESTING_ONLY_NEVER_TRIGGER_EREIGNIS = 'Nur zum Testen: Es werden keine Ereignisse ausgelöst';
    case FOR_TESTING_ONLY_ALWAYS_TRIGGER_EREIGNIS = 'Nur zum Testen: Es werden immer Ereignisse ausgelöst';
}
