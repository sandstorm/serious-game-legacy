<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
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

    public function validate(PlayerId $player, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($player)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst Karten nur überspringen, wenn du dran bist'
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

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($player, new ResourceChanges(zeitsteineChange: -1))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Ressourcen um die Karte zu überspringen',
            );
        }

        $hasFreeTimeSlots = GamePhaseState::hasFreeTimeSlotsForCategory($gameEvents, $this->category);
        if (!$hasFreeTimeSlots) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Es gibt keine freien Zeitsteine mehr.',
            );
        }

        return new AktionValidationResult(canExecute: true);
    }

    public function execute(PlayerId $player, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($player, $gameEvents);
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1747325793);
        }
        return GameEventsToPersist::with(
            new CardWasSkipped($player, $topCardOnPile, $this->pileId, $this->category),
        );
    }
}
