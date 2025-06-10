<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\EreignisId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class ActivateCard implements CommandInterface
{
    public static function create(
        PlayerId     $player,
        CategoryId $category,
    ): ActivateCard {
        return new self($player, $category);
    }

    private function __construct(
        public PlayerId                 $player,
        public CategoryId             $category,
        public ?EreignisId              $attachedEreignis = null,
    ) {
    }

    public function withEreignis(EreignisId $ereignisId): self
    {
        return new self(
            $this->player,
            $this->category,
            $ereignisId,
        );
    }

}
