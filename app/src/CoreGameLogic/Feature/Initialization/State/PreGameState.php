<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\Dto\NameAndLebensziel;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class PreGameState
{

    /**
     * Ready for game if all players have chosen a Name and a Lebensziel
     * @param GameEvents $gameEvents
     * @return bool
     */
    public static function isReadyForGame(GameEvents $gameEvents): bool
    {
        $playersWithNameAndLebensziel = self::playersWithNameAndLebensziel($gameEvents);
        foreach ($playersWithNameAndLebensziel as $nameAndLebensziel) {
            if ($nameAndLebensziel->lebensziel === null ||
                $nameAndLebensziel->name === null
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param GameEvents $gameEvents
     * @return bool
     */
    public static function isInPreGamePhase(GameEvents $gameEvents): bool
    {
        return $gameEvents->findFirstOrNull(GameWasStarted::class) === null;
    }

    /**
     * Ready for game if all players have chosen a Name and a Lebensziel
     * @param GameEvents $gameEvents
     * @return array<string,NameAndLebensziel>
     */
    public static function playersWithNameAndLebensziel(GameEvents $gameEvents): array
    {
        /* @var $playerIdsToNameMap array<string,NameAndLebensziel> */
        $playerIdsToNameMap = [];
        foreach (self::playerIds($gameEvents) as $playerId) {
            // TODO create new object with better naming and maybe different ones for different use cases
            $playerIdsToNameMap[$playerId->value] = new NameAndLebensziel(
                playerId: $playerId,
                name: PlayerState::getNameForPlayerOrNull($gameEvents, $playerId),
                lebensziel: PlayerState::getLebenszielDefinitionForPlayerOrNull($gameEvents, $playerId),
            );
        }
        return $playerIdsToNameMap;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return bool
     */
    public static function hasPlayerName(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        return PlayerState::getNameForPlayerOrNull($gameEvents, $playerId) !== null;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return bool
     */
    public static function hasPlayerLebensziel(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        return PlayerState::getLebenszielDefinitionForPlayerOrNull($gameEvents, $playerId) !== null;
    }

    /**
     * @param GameEvents $gameEvents
     * @return PlayerId[]
     */
    public static function playerIds(GameEvents $gameEvents): array
    {
        return $gameEvents->findFirst(PreGameStarted::class)->playerIds;
    }

    public static function getAmountOfPlayers(GameEvents $gameEvents): int
    {
        return count(self::playerIds($gameEvents));
    }
}
