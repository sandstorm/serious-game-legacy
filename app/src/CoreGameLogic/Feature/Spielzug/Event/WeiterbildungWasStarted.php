<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class WeiterbildungWasStarted implements GameEventInterface, ProvidesResourceChanges, ZeitsteinAktion, DrawsCard
{
    /**
     * @param PlayerId $playerId
     * @param CardId $weiterbildungCardId
     * @param ResourceChanges $resourceChanges
     * @param AnswerOption[] $shuffeldAnswerOptions
     */
    public function __construct(
        public PlayerId        $playerId,
        public CardId          $weiterbildungCardId,
        public ResourceChanges $resourceChanges,
        public array           $shuffeldAnswerOptions
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            weiterbildungCardId: CardId::fromString($values['weiterbildungCardId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
            shuffeldAnswerOptions: array_map(fn($option) => AnswerOption::fromArray($option), $values['shuffeldAnswerOptions']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'weiterbildungCardId' => $this->weiterbildungCardId,
            'resourceChanges' => $this->resourceChanges,
            'shuffeldAnswerOptions' => $this->shuffeldAnswerOptions,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getCategoryId(): CategoryId
    {
        return CategoryId::WEITERBILDUNG;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 1;
    }

    public function getPileId(): PileId
    {
        return new PileId(CategoryId::WEITERBILDUNG, LebenszielPhaseId::ANY_PHASE);
    }
}
