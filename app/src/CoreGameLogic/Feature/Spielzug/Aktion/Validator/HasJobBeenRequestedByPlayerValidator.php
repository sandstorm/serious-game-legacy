<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

/**
 * Succeeds if the given job was in the suggested jobs after the player requested JobOffers. It only considers JobOffers
 * from the current turn.
 */
final class HasJobBeenRequestedByPlayerValidator extends AbstractValidator
{
    private CardId $jobId;

    public function __construct(CardId $jobId)
    {
        $this->jobId = $jobId;
    }

    private function getRequestedJobOffersForThisTurn(GameEvents $gameEvents): ?JobOffersWereRequested
    {
        $eventsThisTurn = AktionsCalculator::forStream($gameEvents)->getEventsThisTurn();
        return $eventsThisTurn->findLastOrNull(JobOffersWereRequested::class);
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $currentJobOffers = $this->getRequestedJobOffersForThisTurn($gameEvents);
        if ($currentJobOffers === null || !in_array($this->jobId->value, array_map(fn ($jobId) => $jobId->value, $currentJobOffers->jobs), true)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Dieser Job wurde dir noch nicht vorgeschlagen'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
