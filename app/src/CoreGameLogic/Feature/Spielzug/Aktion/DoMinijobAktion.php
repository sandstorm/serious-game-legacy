<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
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
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur Minijobs machen, wenn du dran bist'
            );
        }

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($playerId, new ResourceChanges(zeitsteineChange: -1))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Zeitsteine, um den Minijob anzunehmen',
            );
        }

        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class) ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
        $zeitsteinEventsThisTurn = $eventsThisTurn->findAllOfType(ZeitsteinAktion::class);
        if (count($zeitsteinEventsThisTurn) > 0) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
            );
        }
        return new AktionValidationResult(
            canExecute: true,
        );
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
