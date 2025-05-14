<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturzyklus;

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
        );

        $year2 = new KonjunkturzyklusDefinition(
            id: 2,
            type: KonjunkturzyklusTypeEnum::REZESSION,
            description: 'Der neue Präsident einer global bedeutsamen Volkswirtschaft provoziert einen Handelskrieg, was zu einer sinkenden Importnachfrage führt.
Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 5 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
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
        );

        $year3 = new KonjunkturzyklusDefinition(
            id: 3,
            type: KonjunkturzyklusTypeEnum::AUFSCHWUNG,
            description: 'Viele Staaten leiden immer noch unter den Folgen der Finanzkrise. Die Notenbank ändert den Leitszins. Aus diesem Grund kann jede Person zu folgendem Zinnsatz Geld leihen: 0 %
Das geliehene Geld muss innerhalb 20 Raten zurückgezahlt werden, d.h. es werden pro Jahr 5 % des Anfangsbetrags gefordert.
Alle erhalten ihr jährliches Einkommen und begleichen ihre Verbindlichkeiten.',
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
        throw new \RuntimeException('Konjunkturzyklus not found', 1747148685);
    }

}
