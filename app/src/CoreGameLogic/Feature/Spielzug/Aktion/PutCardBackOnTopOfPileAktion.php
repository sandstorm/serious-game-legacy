<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\CanPlayerNotAffordTopCardOnPileValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerNotPlayedACardThisTurnOrPutOneBackValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerSkippedACardThisTurn;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasPutBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * This action allows a player to put a card back on top of a pile, provided they have skipped a card this turn
 * and cannot afford the top card on the pile.
 */
class PutCardBackOnTopOfPileAktion extends Aktion
{
    public function __construct(public CategoryId $category)
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $pileId = new PileId(
            $this->category,
            PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)
        );

        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerSkippedACardThisTurn())
            ->setNext(new HasPlayerNotPlayedACardThisTurnOrPutOneBackValidator())
            ->setNext(new CanPlayerNotAffordTopCardOnPileValidator($pileId));

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1753362843);
        }
        return GameEventsToPersist::with(
            new CardWasPutBackOnTopOfPile($playerId),
        );
    }
}
