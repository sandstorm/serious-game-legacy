<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ZeitsteineForPlayer;

class KonjunkturphaseState
{
    /**
     * @return ZeitsteineForPlayer[]
     */
    public static function calculateInitialZeitsteineForPlayers(GameEvents $gameEvents): array
    {
        $playerIds = $gameEvents->findFirst(GameWasStarted::class)->playerOrdering;
        $numberOfPlayers = count($playerIds);
        $numberOfZeitsteine = match($numberOfPlayers) {
            2 => 6,
            3 => 5,
            4 => 4,
            default => throw new \RuntimeException('Number of players not supported', 1748866080)
        };
        $zeitsteineForPlayers = [];
        foreach ($playerIds as $playerId) {
            $zeitsteineForPlayers[$playerId->value] = new ZeitsteineForPlayer(
                $playerId,
                $numberOfZeitsteine,
            );
        }
        return $zeitsteineForPlayers;
    }
}
