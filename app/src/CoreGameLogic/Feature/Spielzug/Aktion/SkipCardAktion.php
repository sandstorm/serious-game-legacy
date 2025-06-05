<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;

class SkipCardAktion extends Aktion
{
    public function __construct(
        public PileId       $pileId,
        public CardId       $cardId,
        public CategoryEnum $category,
    ) {
        parent::__construct('skip-card', 'Karte überspringen');
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

        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        if (!$topCardOnPile->equals($this->cardId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Nur die oberste Karte auf einem Stapel kann übersprungen werden',
            );
        }

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($player, new ResourceChanges(zeitsteineChange: -1))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Ressourcen um die Karte zu überspringen',
            );
        }

        return new AktionValidationResult(canExecute: true);
    }

    public function execute(PlayerId $player, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($player, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot skip Card: ' . $result->reason, 1747325793);
        }
        return GameEventsToPersist::with(
            new CardWasSkipped($player, $this->cardId, $this->pileId, $this->category),
        );
    }
}
