<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has done at least one Zeitsteinaktion this turn
 * OR if the player does not have any Zeitsteine OR if the player has to skip this round.
 */
final class HasPlayerDoneAtLeastOneZeitsteinaktionThisTurnValidator extends AbstractValidator
{

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);

        $isSkipped = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::AUSSETZEN, false);

        if (
            $eventsThisTurn->findLastOrNull(ZeitsteinAktion::class) === null
            && PlayerState::getZeitsteineForPlayer($gameEvents, $playerId) !== 0
            && $isSkipped === false
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du musst erst einen Zeitstein für eine Aktion ausgeben'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }

}
