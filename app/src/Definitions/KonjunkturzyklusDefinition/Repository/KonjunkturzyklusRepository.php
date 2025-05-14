<?php

declare(strict_types=1);

namespace Domain\Definitions\KonjunkturzyklusDefinition\Repository;

use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;
use Domain\CoreGameLogic\Dto\Enum\KonjunkturzyklusTypeEnum;
use Domain\Definitions\KonjunkturzyklusDefinition\Model\Kompetenzbereich;
use Domain\Definitions\KonjunkturzyklusDefinition\Model\Konjunkturzyklus;

class KonjunkturzyklusRepository
{
    /**
     * @return Konjunkturzyklus[]
     */
    public static function getAllKonjunkturzyklus(): array
    {
        $year1 = new Konjunkturzyklus(
            id: 1,
            type: KonjunkturzyklusTypeEnum::AUFSCHWUNG,
            description: 'Die Wirtschaft wächst langsam aber stetig. Dadurch sind die KonsumentInnen in Kauflaune und steigern die Nachfrage deutlich.
Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 5 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
            leitzins: 5,
            kompetenzbereiche: [
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::BILDUNG,
                    kompetenzsteine: 5,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::FREIZEIT,
                    kompetenzsteine: 4,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::INVESTITIONEN,
                    kompetenzsteine: 4,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::ERWEBSEINKOMMEN,
                    kompetenzsteine: 4,
                ),
            ],
        );

        $year2 = new Konjunkturzyklus(
            id: 2,
            type: KonjunkturzyklusTypeEnum::REZESSION,
            description: 'Der neue Präsident einer global bedeutsamen Volkswirtschaft provoziert einen Handelskrieg, was zu einer sinkenden Importnachfrage führt.
Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 5 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
            leitzins: 5,
            kompetenzbereiche: [
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::BILDUNG,
                    kompetenzsteine: 4,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::FREIZEIT,
                    kompetenzsteine: 5,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::INVESTITIONEN,
                    kompetenzsteine: 3,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::ERWEBSEINKOMMEN,
                    kompetenzsteine: 3,
                ),
            ],
        );

        $year3 = new Konjunkturzyklus(
            id: 3,
            type: KonjunkturzyklusTypeEnum::AUFSCHWUNG,
            description: 'Viele Staaten leiden immer noch unter den Folgen der Finanzkrise. Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 0 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
            leitzins: 0,
            kompetenzbereiche: [
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::BILDUNG,
                    kompetenzsteine: 3,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::FREIZEIT,
                    kompetenzsteine: 6,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::INVESTITIONEN,
                    kompetenzsteine: 3,
                ),
                new Kompetenzbereich(
                    name: KompetenzbereichEnum::ERWEBSEINKOMMEN,
                    kompetenzsteine: 3,
                ),
            ],
        );

        return [
            $year1,
            $year2,
            $year3
        ];
    }


    public static function getRandomKonjunkturzyklus(): Konjunkturzyklus
    {
        $konjunkturzyklen = self::getAllKonjunkturzyklus();
        return $konjunkturzyklen[array_rand($konjunkturzyklen)];
    }

    /**
     * @param int[] $idsOfPastKonjunkturzyklen
     * @return Konjunkturzyklus
     */
    public static function getUnusedRandomKonjunkturzyklus(array $idsOfPastKonjunkturzyklen): Konjunkturzyklus
    {
        $konjunkturzyklen = self::getAllKonjunkturzyklus();
        $unusedKonjunkturzyklen = array_filter($konjunkturzyklen, static fn(Konjunkturzyklus $konjunkturzyklus) => !in_array($konjunkturzyklus->id, $idsOfPastKonjunkturzyklen, true));
        return $unusedKonjunkturzyklen[array_rand($unusedKonjunkturzyklen)];
    }

}
