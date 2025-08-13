<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;

class EnterLebenshaltungskostenForPlayerAktion extends Aktion
{
    public function __construct(private readonly MoneyAmount $input)
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        return new AktionValidationResult(
            canExecute: true,
        );
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot enter Lebenshaltungskosten: ' . $result->reason, 1751373528);
        }
        $expectedInput = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $playerId);
        $previousTries = MoneySheetState::getNumberOfTriesForLebenshaltungskostenInput($gameEvents, $playerId);

        $returnEvents = GameEventsToPersist::with(
            new LebenshaltungskostenForPlayerWereEntered(
                playerId: $playerId,
                playerInput: $this->input,
                expectedInput: $expectedInput,
                wasInputCorrect: $expectedInput->equals($this->input),
            )
        );

        if ($previousTries >= Configuration::MAX_NUMBER_OF_TRIES_PER_INPUT - 1 && !$expectedInput->equals($this->input)) {
            return $returnEvents->withAppendedEvents(
                new LebenshaltungskostenForPlayerWereCorrected($playerId, $expectedInput)
            );
        }

        return $returnEvents;
    }
}
