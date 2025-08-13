<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Succeeds if the given Category has at least one free Zeitsteinslot.
 */
final class HasCategoryFreeZeitsteinslotsValidator extends AbstractValidator
{

    public function __construct(private readonly CategoryId $categoryId, private readonly bool $hasSpecialRulesForActivateCard = false)
    {
    }

    public static function withSpecialRulesForActivateCard(CategoryId $categoryId): self
    {
        return new self($categoryId, true);
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        /**
         * If the player skipped a card we don't need another Zeitsteinslot and can skip this validator.
         * we don't check if the player skipped a card in the same pile here, that is done in @see HasPlayerDoneNoZeitsteinaktionThisTurnValidator
         */
        if ($this->hasSpecialRulesForActivateCard
            && AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound($playerId)) {
            return parent::validate($gameEvents, $playerId);
        }

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
