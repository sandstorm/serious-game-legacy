<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class JobOffersWereRequested implements ZeitsteinAktion, GameEventInterface, ProvidesResourceChanges
{
    /**
     * @param CardId[] $jobs
     */
    public function __construct(
        public PlayerId $player,
        public array $jobs,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            jobs: array_map(fn ($job) => CardId::fromString($job), $values['jobs']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'jobs' => $this->jobs,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->player)) {
            return new ResourceChanges(zeitsteineChange: -1);
        }
        return new ResourceChanges();
    }

    public function getCategoryId(): CategoryId
    {
        return CategoryId::JOBS;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->player;
    }
}
