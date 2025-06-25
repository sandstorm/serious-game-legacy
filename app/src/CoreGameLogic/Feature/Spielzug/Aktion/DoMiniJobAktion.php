<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MiniJobWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\MiniJobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use RuntimeException;

class DoMiniJobAktion extends Aktion
{

    public function __construct(public CardId $miniJobCardId)
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
            throw new RuntimeException('Cannot Do Mini Job: ' . $result->reason, 1749043636);
        }
        /** @var MiniJobCardDefinition $miniJobCardDefinition */
        $miniJobCardDefinition = CardFinder::getInstance()->getCardById($this->miniJobCardId);
        return GameEventsToPersist::with(
            new MiniJobWasStarted($playerId, $miniJobCardDefinition->id),
        );
    }
}

//TODO Test dafür schreiben
