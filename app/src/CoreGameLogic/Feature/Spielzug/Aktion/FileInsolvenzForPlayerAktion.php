<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerANegativeBalanceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerCompletedMoneySheetValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerNoInsuranceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerNoInvestmentsToSellValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerNotInsolventValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasFiledForInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

/**
 * If a player has a negative balance at the end of a Konjunkturphase and no (more) insurances to cancel or investments to sell
 * they have to file for Insolvenz. Their negative balance is set to zero.
 */
class FileInsolvenzForPlayerAktion extends Aktion
{
    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new HasPlayerCompletedMoneySheetValidator();
        $validatorChain
            ->setNext(new IsPlayerNotInsolventValidator())
            ->setNext(new HasPlayerANegativeBalanceValidator())
            ->setNext(new HasPlayerNoInsuranceValidator())
            ->setNext(new HasPlayerNoInvestmentsToSellValidator());

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot file for Insolvenz: ' . $result->reason, 1756801753);
        }

        $currentGuthaben = PlayerState::getGuthabenForPlayer($gameEvents, $playerId);

        return GameEventsToPersist::with(
            new PlayerHasFiledForInsolvenz(
                playerId: $playerId,
                playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $playerId),
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
                resourceChanges: new ResourceChanges()->setGuthabenChange($currentGuthaben->negate()),
            )
        );
    }
}
