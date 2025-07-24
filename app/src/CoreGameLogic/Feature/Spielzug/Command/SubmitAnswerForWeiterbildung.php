<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\AnswerId;

final readonly class SubmitAnswerForWeiterbildung implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        AnswerId $selectedAnswer,
    ):SubmitAnswerForWeiterbildung {
        return new SubmitAnswerForWeiterbildung($playerId, $selectedAnswer);
    }


    private function __construct(
        public PlayerId $playerId,
        public AnswerId $selectedAnswer,
    ) {
    }
}
