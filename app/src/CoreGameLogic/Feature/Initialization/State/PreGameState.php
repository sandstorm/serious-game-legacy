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
     * @param GameEvents $gameStream
     * @return bool
     */
    public static function isReadyForGame(GameEvents $gameStream): bool
    {
        $playersWithNameAndLebensziel = self::playersWithNameAndLebensziel($gameStream);
        foreach ($playersWithNameAndLebensziel as $nameAndLebensziel) {
            if ($nameAndLebensziel->lebensziel === null ||
                $nameAndLebensziel->name === null ||
                PlayerState::getPlayerColor($gameStream, $nameAndLebensziel->playerId) === null
            ) {
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
        foreach (self::playerIds($gameStream) as $playerId) {
            // TODO create new object with better naming and maybe different ones for different use cases
            $playerIdsToNameMap[$playerId->value] = new NameAndLebensziel(
                playerId: $playerId,
                name: self::nameForPlayerOrNull($gameStream, $playerId),
                lebensziel: self::lebenszielForPlayerOrNull($gameStream, $playerId),
            );
        }
        return $playerIdsToNameMap;
    }

    /**
     * @param GameEvents $gameStream
     * @param PlayerId $playerId
     * @return LebenszielDefinition
     */
    public static function lebenszielDefinitionForPlayer(GameEvents $gameStream, PlayerId $playerId): LebenszielDefinition
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
     * @return Lebensziel
     */
    public static function lebenszielForPlayer(GameEvents $gameStream, PlayerId $playerId): Lebensziel
    {
        $lebenszielDefinition = self::lebenszielDefinitionForPlayer($gameStream, $playerId);
        $phases = [];
        foreach ($lebenszielDefinition->phaseDefinitions as $phaseDefinition) {
            $phases[] = LebenszielPhase::fromDefinition($phaseDefinition);
        }
        $lebensziel = new Lebensziel(
            definition: $lebenszielDefinition,
            phases: $phases,
        );
        $kompetenzsteinChanges = $gameStream
            ->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());
        // TODO: place kompetenzsteine in the currently active phase in the future
        $newPhase0 = $lebensziel->phases[0]->withChange($kompetenzsteinChanges);
        return $lebensziel->withUpdatedPhase(0, $newPhase0);
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
