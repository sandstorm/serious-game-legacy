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

final readonly class WeiterbildungWasStarted implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion
{
    public function __construct(
        public PlayerId        $playerId,
        public CardId          $weiterbildungCardId,
        public ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            weiterbildungCardId: CardId::fromString($values['weiterbildungCardId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'weiterbildungCardId' => $this->weiterbildungCardId,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
            return $this->resourceChanges->accumulate(new ResourceChanges(zeitsteineChange: -1));
        }
        return new ResourceChanges();
    }

    public function getCategoryId(): CategoryId
    {
        return CategoryId::WEITERBILDUNGEN;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    // This is a ZeitsteinAktion that does not use a Zeitsteinslot -> return 0
    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 0;
    }
}
