<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

class ActivateCardAktion extends Aktion
{
    public function __construct(public PileId $pileId, public CardId $cardId)
    {
        parent::__construct('skip-card', 'Karte überspringen');
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


        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        if (!$topCardOnPile->equals($this->cardId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Nur die oberste Karte auf einem Stapel kann gespielt werden',
            );
        }


        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($player, $this->getTotalCosts($gameEvents, $this->cardId, $player))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Ressourcen um die Karte zu spielen',
            );
        }

        return new AktionValidationResult(canExecute: true);
    }

    private function getTotalCosts(GameEvents $gameEvents, CardId $cardId, PlayerId $player): ResourceChanges
    {
        $card = CardFinder::getInstance()->getCardById($this->cardId);
        $costToActivate = new ResourceChanges(
            zeitsteineChange: AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound($player) ? 0 : -1
        );
        return $card instanceof KategorieCardDefinition ? $costToActivate->accumulate($card->resourceChanges) : $costToActivate;
    }

    public function execute(PlayerId $player, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($player, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot activate Card: ' . $result->reason, 1748951140);
        }
        return GameEventsToPersist::with(
            new CardWasActivated($player, $this->pileId, $this->cardId, $this->getTotalCosts($gameEvents, $this->cardId, $player))
        );
    }
}
