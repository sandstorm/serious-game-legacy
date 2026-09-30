<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Configuration\Configuration;

final readonly class PreGameStarted implements GameEventInterface, ProvidesResourceChanges
{
    /**
     * @param PlayerId[] $playerIds
     * @param int $definitionsVersion see {@see Configuration::DEFINITIONS_VERSION}
     */
    public function __construct(
        public array $playerIds,
        public ResourceChanges $resourceChanges,
        public int $definitionsVersion = Configuration::DEFINITIONS_VERSION,
    ) {
        foreach ($this->playerIds as $playerId) {
            assert($playerId instanceof PlayerId, 'Player ID must be an instance of PlayerId');
        }
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if (in_array(needle: $playerId, haystack: $this->playerIds, strict: true)) {
            return $this->resourceChanges;
        }
        throw new \RuntimeException('Player ' . $playerId . ' does not exist', 1747827331);
    }

    public static function fromArray(array $values): GameEventInterface
    {
        $playerIds = array_map(fn (string $playerId) => PlayerId::fromString($playerId), $values['playerIds']);
        $resourceChanges = ResourceChanges::fromArray($values['resourceChanges']);
        // games created before the definitions version was introduced don't have one
        $definitionsVersion = $values['definitionsVersion'] ?? 0;
        return new self($playerIds, $resourceChanges, $definitionsVersion);
    }

    public function jsonSerialize(): array
    {
        return [
            'playerIds' => $this->playerIds,
            'resourceChanges' => $this->resourceChanges->jsonSerialize(),
            'definitionsVersion' => $this->definitionsVersion,
        ];
    }
}
