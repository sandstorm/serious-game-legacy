<?php

declare(strict_types=1);

namespace App\Helper;

use App\Livewire\Dto\KompetenzWithColor;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class KompetenzenHelper
{
    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @param float $kompetenzen
     * @param int $requiredKompetenzen
     * @param string $iconComponentName
     * @return KompetenzWithColor[]
     */
    public static function getKompetenzen(GameEvents $gameEvents, PlayerId $playerId, float $kompetenzen, int $requiredKompetenzen, string $iconComponentName): array
    {
        $kompetenzenArray = [];
        for ($i = 0; $i < $kompetenzen; $i++) {
            $kompetenzenArray[] = new KompetenzWithColor(
                drawEmpty: false,
                // only possible for category bildung at the moment
                drawHalfEmpty: abs($i + 0.5 - $kompetenzen) < 0.01,
                colorClass: PlayerState::getPlayerColorClass($gameEvents, $playerId),
                playerName: PlayerState::getNameForPlayer($gameEvents, $playerId),
                iconComponentName: $iconComponentName,
            );
        }

        // fill up the rest with empty ones
        for ($i = 0; $i < $requiredKompetenzen - $kompetenzen; $i++) {
            $kompetenzenArray[] = new KompetenzWithColor(
                drawEmpty: true,
                colorClass: '',
                playerName: '',
                iconComponentName: $iconComponentName,
            );
        }

        return $kompetenzenArray;
    }

}
