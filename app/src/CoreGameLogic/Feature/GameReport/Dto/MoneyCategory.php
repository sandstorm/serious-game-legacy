<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

/**
 * Where the money of a player came from and where it went, in the order shown in the game report.
 */
enum MoneyCategory: string
{
    case STARTKAPITAL = 'Startkapital';
    case GEHALT = 'Gehalt (brutto)';
    case KAPITALERTRAEGE = 'Dividenden und Mieteinnahmen';
    case MINIJOBS = 'Minijobs';
    case VERKAUF_VON_ANLAGEN = 'Verkauf von Anlagen';
    case KREDITE = 'Kredite';
    case STEUERN = 'Steuern und Abgaben';
    case LEBENSHALTUNG = 'Lebenshaltungskosten';
    case LEBENSZIEL = 'Lebensziel';
    case BILDUNG_UND_KARRIERE = 'Bildung & Karriere';
    case FREIZEIT_UND_SOZIALES = 'Freizeit & Soziales';
    case KAUF_VON_ANLAGEN = 'Kauf von Anlagen';
    case KREDITTILGUNG = 'Kredittilgung inkl. Zinsen';
    case VERSICHERUNGEN = 'Versicherungen';
    case STRAFEN = 'Strafen';
    case INSOLVENZ = 'Insolvenz';
    case EREIGNISSE = 'Ereignisse';
    case KONJUNKTUR = 'Konjunktur';
    /** the yearly moneysheet of games that cannot be split anymore, see {@see GameReport::$isFromOlderDefinitionsVersion} */
    case JAHRESABRECHNUNG = 'Jahresabrechnung (netto)';
    case SONSTIGES = 'Sonstiges';
}
