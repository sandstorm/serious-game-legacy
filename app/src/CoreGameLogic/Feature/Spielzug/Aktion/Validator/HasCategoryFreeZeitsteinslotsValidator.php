<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Succeeds if the given Category has at least one free Zeitsteinslot.
 */
final class HasCategoryFreeZeitsteinslotsValidator extends AbstractValidator
{

    private CategoryId $categoryId;

    public function __construct(CategoryId $categoryId)
    {
        $this->categoryId = $categoryId;
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasFreeTimeSlots = GamePhaseState::hasFreeTimeSlotsForCategory($gameEvents, $this->categoryId);
        if (!$hasFreeTimeSlots) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Es gibt keine freien Zeitsteinslots mehr',
            );
        }
        return parent::validate($gameEvents, $playerId);
    }
}
