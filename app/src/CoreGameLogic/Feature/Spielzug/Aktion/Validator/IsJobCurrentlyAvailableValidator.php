<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Succeeds if the given job is currently in the first three cards of the job pile (for the player's phase).
 */
final class IsJobCurrentlyAvailableValidator extends AbstractValidator
{
    private CardId $jobId;

    public function __construct(CardId $jobId)
    {
        $this->jobId = $jobId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $currentJobOffers = PileState::getFirstThreeJobCardIds($gameEvents, new PileId(CategoryId::JOBS, PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)));
        if (!in_array($this->jobId->value, array_map(fn ($jobId) => $jobId->value, $currentJobOffers), true)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Dieser Job wurde dir noch nicht vorgeschlagen'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
