<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

class KonjunkturphaseFinder
{
    /**
     * @return KonjunkturphaseDefinition[]
     */
    public static function getAllKonjunkturphasen(): array
    {
        $year1 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(1),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
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
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    kompetenzsteine: 5,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    kompetenzsteine: 4,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    kompetenzsteine: 4,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
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

        $year2 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(2),
            type: KonjunkturphaseTypeEnum::REZESSION,
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
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    kompetenzsteine: 4,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    kompetenzsteine: 5,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    kompetenzsteine: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
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

        $year3 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(3),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
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
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    kompetenzsteine: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    kompetenzsteine: 6,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    kompetenzsteine: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
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
     * returns a random Konjunkturphase that is not used in the given array of ids aka was not used in the past
     *
     * @param int[] $idsOfPastKonjunkturphasen
     * @return KonjunkturphaseDefinition
     */
    public static function getUnusedRandomKonjunkturphase(array $idsOfPastKonjunkturphasen): KonjunkturphaseDefinition
    {
        $konjunkturphasen = self::getAllKonjunkturphasen();
        $unusedKonjunkturphasen = array_filter($konjunkturphasen, static fn(KonjunkturphaseDefinition $konjunkturphase) => !in_array($konjunkturphase->id->value, $idsOfPastKonjunkturphasen, true));
        return $unusedKonjunkturphasen[array_rand($unusedKonjunkturphasen)];
    }

    /**
     * @param KonjunkturphasenId $id
     * @return KonjunkturphaseDefinition
     */
    public static function findKonjunkturphaseById(KonjunkturphasenId $id): KonjunkturphaseDefinition
    {
        $konjunkturphasen = self::getAllKonjunkturphasen();
        foreach ($konjunkturphasen as $konjunkturphase) {
            if ($konjunkturphase->id === $id) {
                return $konjunkturphase;
            }
        }
        throw new \InvalidArgumentException('Konjunkturphase not found');
    }

}
