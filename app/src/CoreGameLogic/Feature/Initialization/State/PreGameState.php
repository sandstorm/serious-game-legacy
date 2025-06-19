<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\Dto\NameAndLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\LebenszielPhase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;

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
                $nameAndLebensziel->name === null ||
                PlayerState::getPlayerColor($gameEvents, $nameAndLebensziel->playerId) === null
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
                name: self::nameForPlayerOrNull($gameEvents, $playerId),
                lebensziel: self::lebenszielForPlayerOrNull($gameEvents, $playerId),
            );
        }
        return $playerIdsToNameMap;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return LebenszielDefinition
     */
    public static function lebenszielDefinitionForPlayer(GameEvents $gameEvents, PlayerId $playerId): LebenszielDefinition
    {
        $lebensziel = self::lebenszielForPlayerOrNull($gameEvents, $playerId);
        if ($lebensziel === null) {
            throw new \RuntimeException('No Lebensziel found - should never happen.');
        }

        return $lebensziel;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return LebenszielDefinition|null
     */
    public static function lebenszielForPlayerOrNull(GameEvents $gameEvents, PlayerId $playerId): ?LebenszielDefinition
    {
        // @phpstan-ignore property.notFound
        return $gameEvents->findLastOrNullWhere(fn($e) => $e instanceof LebenszielWasSelected && $e->playerId->equals($playerId))?->lebensziel;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return Lebensziel
     */
    public static function lebenszielForPlayer(GameEvents $gameEvents, PlayerId $playerId): Lebensziel
    {
        $lebenszielDefinition = self::lebenszielDefinitionForPlayer($gameEvents, $playerId);
        $phases = [];
        foreach ($lebenszielDefinition->phaseDefinitions as $phaseDefinition) {
            $phases[] = LebenszielPhase::fromDefinition($phaseDefinition);
        }
        $lebensziel = new Lebensziel(
            definition: $lebenszielDefinition,
            phases: $phases,
        );
        $kompetenzsteinChanges = $gameEvents
            ->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());
        // TODO: place kompetenzsteine in the currently active phase in the future
        $newPhase0 = $lebensziel->phases[0]->withChange($kompetenzsteinChanges);
        return $lebensziel->withUpdatedPhase(0, $newPhase0);
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return string
     */
    public static function nameForPlayer(GameEvents $gameEvents, PlayerId $playerId): string
    {
        $name = self::nameForPlayerOrNull($gameEvents, $playerId);
        if ($name === null) {
            throw new \RuntimeException('No Player Name found - should never happen.');
        }

        return $name;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return string|null
     */
    public static function nameForPlayerOrNull(GameEvents $gameEvents, PlayerId $playerId): ?string
    {
        // @phpstan-ignore property.notFound
        return $gameEvents->findLastOrNullWhere(fn($e) => $e instanceof NameForPlayerWasSet && $e->playerId->equals($playerId))?->name;
    }

    /**
     * @param GameEvents $gameEvents
     * @return PlayerId[]
     */
    public static function playerIds(GameEvents $gameEvents): array
    {
        return $gameEvents->findFirst(PreGameStarted::class)->playerIds;
    }
}
