<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\WeiterbildungForm;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartWeiterbildungAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SubmitAnswerForWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;
use Random\Randomizer;

trait HasWeiterbildung
{
    public bool $isWeiterbildungVisible = false;
    public string $validationMessage = '';

    public WeiterbildungForm $weiterbildungForm;

    public function mountHasWeiterbildung(): void
    {
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $currentWeiterbildungsCard = PlayerState::getLastWeiterbildungCardDefinitionForPlayer($this->gameEvents, $this->myself);

        if ($currentWeiterbildungsCard !== null) {
            // TODO check if answer event was fired afterwards
            $this->isWeiterbildungVisible = true;
        }
    }

    public function canStartWeiterbildung(): bool
    {
        $aktion = new StartWeiterbildungAktion();
        return $aktion->validate($this->myself, $this->gameEvents)->canExecute;
    }

    public function showWeiterbildung(): void
    {
        // TODO nachfragen ob starten schon einen Zeitstein kostet-> ja tut es
        $aktion = new StartWeiterbildungAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->myself));
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $this->isWeiterbildungVisible = true;
        $this->broadcastNotify();
    }

    public function closeWeiterbildung(): void
    {
        $this->isWeiterbildungVisible = false;
    }

    public function submitAnswerForWeiterbildung(): void // Objekt wird übergeben und enthält Frage und alle Antwortmöglichkeiten
    {

        // Get the selected answer-Id from the Form
        $selectedAnswerId = $this->weiterbildungForm->answer;
    }


//        $this->validationMessage = $this->weiterbildungForm->answer === 'a' ? 'Alles richtig' : 'Fehler';
//        // TODO validate answer, a is always correct
//
//        // TODO if correct, write new event with resource change
//
//    }

}
