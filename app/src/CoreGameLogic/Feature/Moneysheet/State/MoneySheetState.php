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
        $job = PlayerState::getJobForPlayer($gameEvents, $playerId);
        if ($job !== null) {
            return max([intval(round($job->gehalt->value * 0.35)), $minKosten]);
        }
        return $minKosten;
    }

    public static function calculateSteuernUndAbgabenForPlayer(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $job = PlayerState::getJobForPlayer($gameEvents, $playerId);
        if ($job !== null) {
            return intval(round($job->gehalt->value * 0.25));
        }
        return 0;
    }
}
