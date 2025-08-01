<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel;

use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use RuntimeException;

class LebenszielFinder
{
    /**
     * @return LebenszielDefinition[]
     */
    public static function getAllLebensziele(): array
    {
        $lebensziel1 = new LebenszielDefinition(
            id: LebenszielId::create(1),
            name: 'Aufforstung der Sahara in Niger',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Damit du dein Projekt verwirklichen kannst ist es wichtig, das du das Land Niger richtig kennenlernst. Dafür musst du folgender Voraussetzungen erfüllen:',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'In dieser Phase musst du das nötige Grundkapital für dein Projekt aufbringen:',
                    investitionen: new MoneyAmount(250000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Du has das notwendige Kapital aufgebracht. Nun musst du den Grundstein für die Aufforstungsstation legen unter folgenden Voraussetzungen:',
                    investitionen: new MoneyAmount(1000000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 3,
                ),
            ]
        );

        $lebensziel2 = new LebenszielDefinition(
            id: LebenszielId::create(2),
            name: 'TODO',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Damit du dein Projekt verwirklichen kannst ist es wichtig, das du das Land Niger richtig kennenlernst. Dafür musst du folgender Voraussetzungen erfüllen:',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'In dieser Phase musst du das nötige Grundkapital für dein Projekt aufbringen:',
                    investitionen: new MoneyAmount(250000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Du has das notwendige Kapital aufgebracht. Nun musst du den Grundstein für die Aufforstungsstation legen unter folgenden Voraussetzungen:',
                    investitionen: new MoneyAmount(1000000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 5,
                ),
            ]
        );

        return [
            $lebensziel1,
            $lebensziel2,
        ];
    }

    public static function findLebenszielById(LebenszielId $id): LebenszielDefinition
    {
        $lebensziele = self::getAllLebensziele();
        foreach ($lebensziele as $lebensziel) {
            if ($lebensziel->id === $id) {
                return $lebensziel;
            }
        }

        throw new RuntimeException('Lebensziel ' . $id . ' not found', 1747642070);
    }

}
