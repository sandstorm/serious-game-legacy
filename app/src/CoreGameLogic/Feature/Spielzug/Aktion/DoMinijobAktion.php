<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneNoZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughZeitsteineValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;
use RuntimeException;

class DoMinijobAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('do-minijiob','Minijob machen');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerEnoughZeitsteineValidator(1))
            ->setNext(new HasPlayerDoneNoZeitsteinaktionThisTurnValidator());

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot Do minijob: ' . $result->reason, 1750854280);
        }
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, PileId::MINIJOBS_PHASE_1);

        /** @var MinijobCardDefinition $minijobCardDefinition */
        $minijobCardDefinition = CardFinder::getInstance()->getCardById($topCardOnPile);
        return GameEventsToPersist::with(
            new MinijobWasDone($playerId, $minijobCardDefinition->id, $minijobCardDefinition->resourceChanges),
        );
    }
}
