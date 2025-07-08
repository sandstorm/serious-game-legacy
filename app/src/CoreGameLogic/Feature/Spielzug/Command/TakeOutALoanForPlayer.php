<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class TakeOutALoanForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        TakeOutALoanForm $takeOutLoanForm
    ): TakeOutALoanForPlayer
    {
        return new self($playerId, $takeOutLoanForm);
    }

    private function __construct(
        public PlayerId $playerId,
        public TakeOutALoanForm $takeOutLoanForm
    ) {
    }
}
