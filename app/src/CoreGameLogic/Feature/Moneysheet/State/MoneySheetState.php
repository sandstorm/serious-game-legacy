<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class MoneySheetState
{
    public static function lebenshaltungskostenForPlayer(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $minKosten = 5000;
        $kosten = 0;
        $job = PlayerState::getJobForPlayer($gameEvents, $playerId);
        if ($job !== null) {
            $kosten = intval(round($job->gehalt->value * 0.35));
        }
        return max([$kosten, $minKosten]);
    }
}
