<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasCategoryFreeZeitsteinslotsValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class SkipCardAktion extends Aktion
{
    private PileId $pileId;

    public function __construct(
        public CategoryId $category,
    ) {
        parent::__construct('skip-card', 'Karte überspringen');
        $this->pileId = PileState::getPileIdForCategoryAndPhase($this->category);
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator())
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasCategoryFreeZeitsteinslotsValidator($this->category));
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot skip card: ' . $result->reason, 1747325793);
        }
        return GameEventsToPersist::with(
            new CardWasSkipped($playerId, $topCardOnPile, $this->pileId, $this->category),
        );
    }
}
