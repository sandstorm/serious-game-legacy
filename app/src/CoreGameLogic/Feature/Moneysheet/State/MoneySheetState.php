<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class MoneySheetState
{
    public static function calculateLebenshaltungskostenForPlayer(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $minKosten = 5000;
        $gehalt = PlayerState::getGehaltForPlayer($gameEvents, $playerId);
        return max([intval(round($gehalt * 0.35)), $minKosten]);
    }

    public static function calculateSteuernUndAbgabenForPlayer(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $gehalt = PlayerState::getGehaltForPlayer($gameEvents, $playerId);
        return intval(round($gehalt * 0.25));
    }
}
