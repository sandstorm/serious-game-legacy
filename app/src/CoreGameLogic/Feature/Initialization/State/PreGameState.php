<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGamePhaseStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\Dto\NameAndLebensziel;

class PreGameState
{

    /**
     * Ready for game if all players have chosen a Name and a Lebensziel
     * @param GameEvents $gameStream
     * @return bool
     */
    public static function isReadyForGame(GameEvents $gameStream): bool
    {
        $playersWithNameAndLebensziel = self::playersWithNameAndLebensziel($gameStream);
        foreach ($playersWithNameAndLebensziel as $playerId) {
            $nameAndLebensziel = $playersWithNameAndLebensziel[$playerId];
            if ($nameAndLebensziel->lebensziel === null || $nameAndLebensziel->name === null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Ready for game if all players have chosen a Name and a Lebensziel
     * @param GameEvents $gameStream
     * @return \SplObjectStorage<PlayerId,NameAndLebensziel>
     */
    public static function playersWithNameAndLebensziel(GameEvents $gameStream): \SplObjectStorage
    {
        /* @var $playerIdsToNameMap \SplObjectStorage<PlayerId,NameAndLebensziel> */
        $playerIdsToNameMap = new \SplObjectStorage();
        foreach (self::playerIds($gameStream) as $playerId) {
            $playerIdsToNameMap->attach($playerId, new NameAndLebensziel(
                name: self::nameForPlayerOrNull($gameStream, $playerId),
                lebensziel: self::lebenszielForPlayerOrNull($gameStream, $playerId),
            ));
        }

        // @phpstan-ignore return.type
        return $playerIdsToNameMap;
    }

    public static function lebenszielForPlayer(GameEvents $gameStream, PlayerId $playerId): Lebensziel
    {
        $lebensziel = self::lebenszielForPlayerOrNull($gameStream, $playerId);
        if ($lebensziel === null) {
            throw new \RuntimeException('No Lebensziel found - should never happen.');
        }

        return $lebensziel;
    }
    public static function lebenszielForPlayerOrNull(GameEvents $gameStream, PlayerId $playerId): ?Lebensziel
    {
        // @phpstan-ignore property.notFound
        return $gameStream->findLastOrNullWhere(fn($e) => $e instanceof LebenszielChosen && $e->playerId->equals($playerId))?->lebensziel;
    }

    public static function nameForPlayer(GameEvents $gameStream, PlayerId $playerId): string
    {
        $name = self::nameForPlayerOrNull($gameStream, $playerId);
        if ($name === null) {
            throw new \RuntimeException('No Player Name found - should never happen.');
        }

        return $name;
    }
    public static function nameForPlayerOrNull(GameEvents $gameStream, PlayerId $playerId): ?string
    {
        // @phpstan-ignore property.notFound
        return $gameStream->findLastOrNullWhere(fn($e) => $e instanceof NameForPlayerWasSet && $e->playerId->equals($playerId))?->name;
    }


    /**
     * @param GameEvents $gameStream
     * @return PlayerId[]
     */
    public static function playerIds(GameEvents $gameStream): array
    {
        return $gameStream->findFirst(PreGamePhaseStarted::class)->playerIds;
    }
}
