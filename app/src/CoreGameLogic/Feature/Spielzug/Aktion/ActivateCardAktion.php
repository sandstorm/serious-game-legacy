<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class ActivateCardAktion extends Aktion
{
    private CardDefinition $card;
    private PileId $pileId;

    public function __construct(public CategoryId $category)
    {
        parent::__construct('activate-card', 'Karte Spielen');

        // TODO reorganize piles -> Category and phase separate -> `PileState::topCardIdForPile($gameEvents, PileId::BILDUNG, phase: 1)`
        $this->pileId = PileState::getPileIdForCategoryAndPhase($category);
    }

    /**
     * Returns false, if the player performed any Aktionen this turn, that prevent this Aktion (e.g. another ActivateCard).
     *
     * @param GameEvents $gameEvents
     * @return AktionValidationResult
     */
    private function validateWithPreviousActions(GameEvents $gameEvents): AktionValidationResult
    {
        $eventsThisTurn = AktionsCalculator::forStream($gameEvents)->getEventsThisTurn();
        $zeitsteinEventsThisTurn = $eventsThisTurn->findAllOfType(ZeitsteinAktion::class);
        if (count($zeitsteinEventsThisTurn) === 0) {
            return new AktionValidationResult(true);
        }

        if (count($zeitsteinEventsThisTurn) > 1 || $zeitsteinEventsThisTurn->findFirstOrNull(CardWasSkipped::class) === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast bereits eine andere Aktion ausgeführt'
            );
        }

        if ($zeitsteinEventsThisTurn->findFirst(CardWasSkipped::class)->categoryId->value !== $this->category->value) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast bereits eine Karte in einer anderen Kategorie übersprungen'
            );
        }

        return new AktionValidationResult(true);
    }

    public function validate(PlayerId $player, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($player)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst Karten nur spielen, wenn du dran bist'
            );
        }

        $validationResultAfterPreviousActions = $this->validateWithPreviousActions($gameEvents);
        if (!$validationResultAfterPreviousActions->canExecute) {
            return $validationResultAfterPreviousActions;
        }

        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        $this->card = CardFinder::getInstance()->getCardById($topCardOnPile);

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($player,
            $this->getTotalCosts($gameEvents, $player))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Ressourcen um die Karte zu spielen',
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

    private function getTotalCosts(GameEvents $gameEvents, PlayerId $player): ResourceChanges
    {
        $costToActivate = new ResourceChanges(
            zeitsteineChange: AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound($player) ? 0 : -1
        );
        return $this->card instanceof KategorieCardDefinition ? $costToActivate->accumulate($this->card->resourceChanges) : $costToActivate;
    }

    public function execute(PlayerId $player, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($player, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1748951140);
        }
        return GameEventsToPersist::with(
            new CardWasActivated(
                $player,
                $this->card->getPileId(),
                $this->card->getId(),
                $this->category,
                $this->getTotalCosts($gameEvents, $player)
            )
        );
    }
}
