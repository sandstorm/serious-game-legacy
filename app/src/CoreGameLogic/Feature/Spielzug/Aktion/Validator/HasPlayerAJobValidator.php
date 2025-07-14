<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

final class HasPlayerAJobValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        $playersJob = PlayerState::getJobForPlayer($gameEvents, $currentPlayer);
        if (!$currentPlayer->equals($playerId) && $playersJob) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast schon einen Job'
            );
        }
        return parent::validate($gameEvents, $playerId);
    }
}
