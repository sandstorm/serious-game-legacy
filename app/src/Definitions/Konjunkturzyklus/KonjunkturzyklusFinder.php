<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturzyklus;

use Domain\Definitions\Auswirkung\AuswirkungDefinition;
use Domain\Definitions\Auswirkung\Enum\AuswirkungScopeEnum;
use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;
use Domain\Definitions\Kompetenzbereich\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturzyklus\Enum\KonjunkturzyklusTypeEnum;

class KonjunkturzyklusFinder
{
    /**
     * @return KonjunkturzyklusDefinition[]
     */
    public static function getAllKonjunkturzyklen(): array
    {
        $year1 = new KonjunkturzyklusDefinition(
            id: 1,
            type: KonjunkturzyklusTypeEnum::AUFSCHWUNG,
            description: 'Die Wirtschaft wächst langsam aber stetig. Dadurch sind die KonsumentInnen in Kauflaune und steigern die Nachfrage deutlich.
Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 5 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
            additionalEvents: 'Immobilienmarkt - die jähliche Grundsteuer für Immobilien
wird fällig. 1000 €/Immobilie müssen bezahlt werden.

Der steigende Leitzins erhöht die Deflation, die Kaufkraft der Barreserven erhöht sich: Die auf den Karten angegebenen Kosten müssen in diesem Jahr nur zu 90 % beglichen werden.',
            leitzins: 5,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::BILDUNG,
                    kompetenzsteine: 5,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::FREIZEIT,
                    kompetenzsteine: 4,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::INVESTITIONEN,
                    kompetenzsteine: 4,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::ERWEBSEINKOMMEN,
                    kompetenzsteine: 4,
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BILDUNG,
                    modifier: '90 % der Kosten',
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::FREIZEIT,
                    modifier: '90 % der Kosten',
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::INVESTITIONEN,
                    modifier: '-1000 €/Immobilie',
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::INVESTITIONEN,
                    modifier: '90 % der Kosten',
                ),
            ]
        );

        $year2 = new KonjunkturzyklusDefinition(
            id: 2,
            type: KonjunkturzyklusTypeEnum::REZESSION,
            description: 'Der neue Präsident einer global bedeutsamen Volkswirtschaft provoziert einen Handelskrieg, was zu einer sinkenden Importnachfrage führt.
Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 5 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
            additionalEvents: 'An der Börse herrscht große Unsicherheit und der Aktienindex
verliert zunehmend an Wert. Jede verliert 20 % ihrer Aktien.

Die Regierung fördert eine neue Bilungsoffensive. Jede erhält - wenn gewünscht - eine Karte (Bildung/Karriere), ohne einen Zeitstein setzten zu müssen. ',
            leitzins: 5,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::BILDUNG,
                    kompetenzsteine: 4,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::FREIZEIT,
                    kompetenzsteine: 5,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::INVESTITIONEN,
                    kompetenzsteine: 3,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::ERWEBSEINKOMMEN,
                    kompetenzsteine: 3,
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BILDUNG,
                    modifier: 'eine Karte ohne Zeitstein',
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::INVESTITIONEN,
                    modifier: 'Jede verliert 20 % ihrer Aktien. ',
                ),
            ]
        );

        $year3 = new KonjunkturzyklusDefinition(
            id: 3,
            type: KonjunkturzyklusTypeEnum::AUFSCHWUNG,
            description: 'Viele Staaten leiden immer noch unter den Folgen der Finanzkrise. Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 0 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
            additionalEvents: 'Durch den niedrigen Leitzins erhöht sich die Geldmenge
und damit auch die Lebenshaltungskosten, was zu einer
Steigerung der Inflation führt.
Jede zahlt 2.000 € zusätzlich.

Eine Naturkatastrophe bricht über das Land hinein: Sturm
Olga verwüstet ganze Städte. Die Bewohnerinnen werden
dazu aufgerufen, bei den Räumungsarbeiten zu helfen (Kosten = 1 Zeitstein). Alle haben in dieser Runde nur 2 Zeitsteine zur Verfügung. ',
            leitzins: 0,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::BILDUNG,
                    kompetenzsteine: 3,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::FREIZEIT,
                    kompetenzsteine: 6,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::INVESTITIONEN,
                    kompetenzsteine: 3,
                ),
                new KompetenzbereichDefinition(
                    name: KompetenzbereichEnum::ERWEBSEINKOMMEN,
                    kompetenzsteine: 3,
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::ZEITSTEINE,
                    modifier: '-1 Zeitstein',
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LEBENSERHALTUNGSKOSTEN,
                    modifier: '-2.000 € zusätzlich',
                )
            ]
        );

        return [
            $year1,
            $year2,
            $year3
        ];
    }

    /**
     * returns a random Konjunkturzyklus that is not used in the given array of ids aka was not used in the past
     *
     * @param int[] $idsOfPastKonjunkturzyklen
     * @return KonjunkturzyklusDefinition
     */
    public static function getUnusedRandomKonjunkturzyklus(array $idsOfPastKonjunkturzyklen): KonjunkturzyklusDefinition
    {
        $konjunkturzyklen = self::getAllKonjunkturzyklen();
        $unusedKonjunkturzyklen = array_filter($konjunkturzyklen, static fn(KonjunkturzyklusDefinition $konjunkturzyklus) => !in_array($konjunkturzyklus->id, $idsOfPastKonjunkturzyklen, true));
        return $unusedKonjunkturzyklen[array_rand($unusedKonjunkturzyklen)];
    }

    /**
     * @param int $id
     * @return KonjunkturzyklusDefinition
     */
    public static function findKonjunkturZyklusById(int $id): KonjunkturzyklusDefinition
    {
        $konjunkturzyklen = self::getAllKonjunkturzyklen();
        foreach ($konjunkturzyklen as $konjunkturzyklus) {
            if ($konjunkturzyklus->id === $id) {
                return $konjunkturzyklus;
            }
        }
        throw new \InvalidArgumentException('Konjunkturzyklus not found');
    }

}
