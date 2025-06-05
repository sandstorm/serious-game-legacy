<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\EreignisId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;

final readonly class ActivateCard implements CommandInterface
{
    public static function create(
        PlayerId     $player,
        CardId       $cardId,
        PileId       $pile,
        CategoryEnum $category,
    ): ActivateCard {
        return new self($player, $cardId, $pile, $category);
    }

    private function __construct(
        public PlayerId                 $player,
        public CardId                   $cardId,
        public PileId                   $pile,
        public CategoryEnum             $category,
        public ?EreignisId              $attachedEreignis = null,
        public ?KategorieCardDefinition $fixedCardDefinitionForTesting = null,
    ) {
    }

    public function withEreignis(EreignisId $ereignisId): self
    {
        return new self(
            $this->player,
            $this->cardId,
            $this->pile,
            $this->category,
            $ereignisId,
            $this->fixedCardDefinitionForTesting
        );
    }

    public function withFixedCardDefinitionForTesting (KategorieCardDefinition $cardDefinition): self
    {
        return new self(
            $this->player,
            $cardDefinition->id,
            $this->pile,
            $this->category,
            $this->attachedEreignis,
            $cardDefinition
        );
    }
}
