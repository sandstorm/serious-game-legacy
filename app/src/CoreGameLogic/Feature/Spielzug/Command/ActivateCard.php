<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Cards\Model\CardDefinition;

final readonly class ActivateCard implements CommandInterface
{
    public static function create(
        PlayerId $player,
        CardId $cardId,
        PileId $pile,
    ): ActivateCard {
        return new self($player, $cardId, $pile);
    }

    private function __construct(
        public PlayerId $player,
        public CardId $cardId,
        public PileId $pile,
        public ?EreignisId $attachedEreignis = null,
        public ?CardDefinition $fixedCardDefinitionForTesting = null,
    ) {
    }

    public function withEreignis(EreignisId $ereignisId): self
    {
        return new self(
            $this->player,
            $this->cardId,
            $this->pile,
            $ereignisId,
            $this->fixedCardDefinitionForTesting
        );
    }

    public function withFixedCardDefinitionForTesting (CardDefinition $cardDefinition): self
    {
        return new self(
            $this->player,
            $cardDefinition->id,
            $this->pile,
            $this->attachedEreignis,
            $cardDefinition
        );
    }
}
