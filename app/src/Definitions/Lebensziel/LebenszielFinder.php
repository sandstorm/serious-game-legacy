<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel;

use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;

class LebenszielFinder
{
    /**
     * @return LebenszielDefinition[]
     */
    public static function getAllLebensziele(): array
    {
        $lebensziel1 = new LebenszielDefinition(
            id: 1,
            name: 'Aufforstung der Sahara in Niger',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    phase: 1,
                    description: 'Damit du dein Projekt verwirklichen kannst ist es wichtig, das du das Land Niger richtig kennenlernst. Dafür musst du folgender Voraussetzungen erfüllen:',
                    invenstition: 50000,
                    erwerbseinkommen: 65000,
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    phase: 2,
                    description: 'In dieser Phase musst du das nötige Grundkapital für dein Projekt aufbringen:',
                    invenstition: 250000,
                    erwerbseinkommen: 0,
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    phase: 3,
                    description: 'Du has das notwendige Kapital aufgebracht. Nun musst du den Grundstein für die Aufforstungsstation legen unter folgenden Voraussetzungen:',
                    invenstition: 1000000,
                    erwerbseinkommen: 0,
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 3,
                ),
            ]
        );

        $lebensziel2 = new LebenszielDefinition(
            id: 2,
            name: 'TODO',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    phase: 1,
                    description: 'Damit du dein Projekt verwirklichen kannst ist es wichtig, das du das Land Niger richtig kennenlernst. Dafür musst du folgender Voraussetzungen erfüllen:',
                    invenstition: 50000,
                    erwerbseinkommen: 65000,
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    phase: 2,
                    description: 'In dieser Phase musst du das nötige Grundkapital für dein Projekt aufbringen:',
                    invenstition: 250000,
                    erwerbseinkommen: 0,
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    phase: 3,
                    description: 'Du has das notwendige Kapital aufgebracht. Nun musst du den Grundstein für die Aufforstungsstation legen unter folgenden Voraussetzungen:',
                    invenstition: 1000000,
                    erwerbseinkommen: 0,
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 3,
                ),
            ]
        );

        return [
            $lebensziel1,
            $lebensziel2,
        ];
    }

    /**
     * @param int $id
     * @return LebenszielDefinition
     */
    public static function findLebenszielById(int $id): LebenszielDefinition
    {
        $lebensziele = self::getAllLebensziele();
        foreach ($lebensziele as $lebensziel) {
            if ($lebensziel->id === $id) {
                return $lebensziel;
            }
        }

        throw new \RuntimeException('Lebensziel ' . $id . ' not found', 1747642070);
    }

}
