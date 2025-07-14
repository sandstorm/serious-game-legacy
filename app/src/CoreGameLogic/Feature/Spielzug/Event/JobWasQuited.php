<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class JobWasQuited implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        //public array    $jobs,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            //jobs: array_map(fn ($job) => CardId::fromString($job), $values['jobs']),
        );
    }
    public function jsonSerialize(): array
    {
        return[
        'playerId' => $this->playerId,
        //'jobs' => $this->jobs,
        ];
    }

//    public function getCategoryId(): CategoryId
//    {
//        return CategoryId::JOBS;
//    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }
}
