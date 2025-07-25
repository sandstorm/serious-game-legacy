<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class AnswerForWeiterbildungWasSubmitted implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId        $playerId,
        public CardId          $weiterbildungCardId,
        public AnswerId        $selectedAnswerId,
        public bool            $wasCorrect,
        public ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            weiterbildungCardId:  CardId::fromString($values['weiterbildungCardId']),
            selectedAnswerId: AnswerId::fromString($values['selectedAnswerId']),
            wasCorrect: $values['wasCorrect'],
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'weiterbildungCardId' => $this->weiterbildungCardId,
            'selectedAnswerId' => $this->selectedAnswerId,
            'wasCorrect' => $this->wasCorrect,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
            return $this->resourceChanges->accumulate(new ResourceChanges(bildungKompetenzsteinChange: +1));
        }
        return new ResourceChanges();
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }
}
