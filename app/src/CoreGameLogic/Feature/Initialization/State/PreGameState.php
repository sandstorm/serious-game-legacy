<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\Dto\NameAndLebensziel;
use Domain\Definitions\Lebensziel\LebenszielDefinition;

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
        foreach ($playersWithNameAndLebensziel as $nameAndLebensziel) {
            if ($nameAndLebensziel->lebensziel === null || $nameAndLebensziel->name === null) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param GameEvents $gameStream
     * @return bool
     */
    public static function isInPreGamePhase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(GameWasStarted::class) === null;
    }

    /**
     * Ready for game if all players have chosen a Name and a Lebensziel
     * @param GameEvents $gameStream
     * @return array<string,NameAndLebensziel>
     */
    public static function playersWithNameAndLebensziel(GameEvents $gameStream): array
    {
        /* @var $playerIdsToNameMap array<string,NameAndLebensziel> */
        $playerIdsToNameMap = [];
        $playerOrder = 1;
        foreach (self::playerIds($gameStream) as $playerId) {

            // TODO create new object with better naming and maybe different ones for different use cases
            $playerIdsToNameMap[$playerId->value] = new NameAndLebensziel(
                order: $playerOrder,
                playerId: $playerId,
                name: self::nameForPlayerOrNull($gameStream, $playerId),
                lebensziel: self::lebenszielForPlayerOrNull($gameStream, $playerId),
            );

            $playerOrder++;
        }
        return $playerIdsToNameMap;
    }

    /**
     * @param GameEvents $gameStream
     * @param PlayerId $playerId
     * @return LebenszielDefinition
     */
    public static function lebenszielForPlayer(GameEvents $gameStream, PlayerId $playerId): LebenszielDefinition
    {
        $lebensziel = self::lebenszielForPlayerOrNull($gameStream, $playerId);
        if ($lebensziel === null) {
            throw new \RuntimeException('No Lebensziel found - should never happen.');
        }

        return $lebensziel;
    }

    /**
     * @param GameEvents $gameStream
     * @param PlayerId $playerId
     * @return LebenszielDefinition|null
     */
    public static function lebenszielForPlayerOrNull(GameEvents $gameStream, PlayerId $playerId): ?LebenszielDefinition
    {
        // @phpstan-ignore property.notFound
        return $gameStream->findLastOrNullWhere(fn($e) => $e instanceof LebenszielWasSelected && $e->playerId->equals($playerId))?->lebensziel;
    }

    /**
     * @param GameEvents $gameStream
     * @param PlayerId $playerId
     * @return string
     */
    public static function nameForPlayer(GameEvents $gameStream, PlayerId $playerId): string
    {
        $name = self::nameForPlayerOrNull($gameStream, $playerId);
        if ($name === null) {
            throw new \RuntimeException('No Player Name found - should never happen.');
        }

        return $name;
    }

    /**
     * @param GameEvents $gameStream
     * @param PlayerId $playerId
     * @return string|null
     */
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
        return $gameStream->findFirst(PreGameStarted::class)->playerIds;
    }
}
