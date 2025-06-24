<?php

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\PlayerId;

class ActivateMiniJobAktion extends Aktion
{

    public function __construct()
    {
        parent::__construct('activate-minijiob','Minijob aktivieren');
    }

    public function validate(PlayerId $player, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($player)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur Minijobs machen, wenn du dran bist'
            );
        }

        //TODO: reason: 'Du kannst immer nur den angezeigten Minijob machen.'
        //TODO: reason: 'Du hast nicht genug Zietsteine um den Minijob anzunehmen.'
        //TODO:

}
