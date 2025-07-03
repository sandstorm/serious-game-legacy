<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\DoesPlayerMeetJobRequirementsValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasJobBeenRequestedByPlayerValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;

class AcceptJobOffersAktion extends Aktion
{
    public function __construct(public CardId $jobId)
    {
        parent::__construct('request-job-offers', 'Jobs anzeigen');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasJobBeenRequestedByPlayerValidator($this->jobId))
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new DoesPlayerMeetJobRequirementsValidator($this->jobId));

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot Accept Job Offer: ' . $result->reason, 1749043636);
        }
        /** @var JobCardDefinition $job */
        $job = CardFinder::getInstance()->getCardById($this->jobId);
        return GameEventsToPersist::with(
            new JobOfferWasAccepted($playerId, $job->id, $job->gehalt),
        );
    }
}
