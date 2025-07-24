<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\CanPlayerAffordTopCardOnPileValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\CanPlayerNotAffordTopCardOnPileValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerSkippedACardThisTurn;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasDiscarded;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class DiscardCardAktion extends Aktion
{
    private PileId $pileId;

    public function __construct(public CategoryId $category)
    {
        parent::__construct('discard-card', 'Karte Ablegen');

        $this->pileId = PileState::getPileIdForCategoryAndPhase($category);
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerSkippedACardThisTurn())
            ->setNext(new CanPlayerNotAffordTopCardOnPileValidator($this->pileId));
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1753362843);
        }
        return GameEventsToPersist::with(
            new CardWasDiscarded($playerId),
        );
    }
}
