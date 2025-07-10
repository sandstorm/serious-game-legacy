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
            zinssatz: 5,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitsteinslots: 5,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitsteinslots: 4,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitsteinslots: 4,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitsteinslots: 4,
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
            zinssatz: 5,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitsteinslots: 4,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitsteinslots: 5,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitsteinslots: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitsteinslots: 3,
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BILDUNG,
                    modifier: 'eine Karte ohne Zeitstein',
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::INVESTITIONEN,
                    modifier: 'Jede verliert 20 % ihrer Aktien.',
                ),
            ]
        );

        $year3 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(3),
            type: KonjunkturphaseTypeEnum::BOOM,
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
            zinssatz: 0,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitsteinslots: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitsteinslots: 6,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitsteinslots: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitsteinslots: 3,
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

        $year4 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(3),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
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
            zinssatz: 0,
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitsteinslots: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitsteinslots: 6,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitsteinslots: 3,
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitsteinslots: 3,
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
            $year3,
            $year4,
        ];
    }

    /**
     * returns a random Konjunkturphase
     *
     * @param KonjunkturphaseTypeEnum|null $lastType
     * @return KonjunkturphaseDefinition
     */
    public static function getRandomKonjunkturphase(?KonjunkturphaseTypeEnum $lastType): KonjunkturphaseDefinition
    {
        $possibleNextPhaseTypes = self::getListOfPossibleNextPhaseTypes($lastType);
        $konjunkturphasen = self::getAllKonjunkturphasenByTypes($possibleNextPhaseTypes);
        return $konjunkturphasen[array_rand($konjunkturphasen)];
    }

    /**
     * @param KonjunkturphaseTypeEnum[] $types
     * @return KonjunkturphaseDefinition[]
     */
    public static function getAllKonjunkturphasenByTypes(array $types): array
    {
        $allKonjunkturphasen = self::getAllKonjunkturphasen();
        return array_filter($allKonjunkturphasen, static fn(KonjunkturphaseDefinition $konjunkturphase) => in_array($konjunkturphase->type, $types, true));
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

    /**
     * Public for testing purposes only.
     * Returns a list of possible next Konjunkturphasen types based on the current type.
     *
     * @param KonjunkturphaseTypeEnum|null $konjunkturphaseType
     * @return KonjunkturphaseTypeEnum[]
     * @internal
     */
    public static function getListOfPossibleNextPhaseTypes(?KonjunkturphaseTypeEnum $konjunkturphaseType = null): array
    {
        return match ($konjunkturphaseType) {
            KonjunkturphaseTypeEnum::AUFSCHWUNG => [
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
                KonjunkturphaseTypeEnum::BOOM,
                KonjunkturphaseTypeEnum::REZESSION,
            ],
            KonjunkturphaseTypeEnum::BOOM => [
                KonjunkturphaseTypeEnum::BOOM,
                KonjunkturphaseTypeEnum::DEPRESSION,
                KonjunkturphaseTypeEnum::REZESSION,
            ],
            KonjunkturphaseTypeEnum::REZESSION => [
                KonjunkturphaseTypeEnum::REZESSION,
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
                KonjunkturphaseTypeEnum::DEPRESSION,
            ],
            KonjunkturphaseTypeEnum::DEPRESSION => [
                KonjunkturphaseTypeEnum::DEPRESSION,
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
            ],
            default => [
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
            ]
        };
    }
}
