<?php
declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\AnswerForWeiterbildungWasSubmitted;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Random\Randomizer;

class WeiterbildungModal extends Component
{

    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $weiterbildungCardDefinition = PlayerState::getLastWeiterbildungCardDefinitionForPlayer(
            $this->gameEvents,
            $this->playerId
        );

        $weiterbildungsOptions = PlayerState::getLastWeiterbildungsEventForPlayer(
            $this->gameEvents,
            $this->playerId)?->shuffeldAnswerOptions;

        $submittedAnswerEvent = $this->gameEvents->findLast(
            AnswerForWeiterbildungWasSubmitted::class
        );

        if($submittedAnswerEvent) {
            return view('components.gameboard.weiterbildung-result-modal', [
                'weiterbildung' => $weiterbildungCardDefinition,
                'playerId' => $this->playerId,
                'answerOptions' => $weiterbildungsOptions,
                'correctAnswerId' =>$submittedAnswerEvent->wasCorrect ? $submittedAnswerEvent->selectedAnswerId : $weiterbildungCardDefinition->getCorrectAnswerId(),
                'submittedAnswerId' => $submittedAnswerEvent->submittedAnswerId,
                'wasCorrect' => $submittedAnswerEvent->wasCorrect,
            ]);
        }

        return view('components.gameboard.weiterbildung-modal', [ // gerendert wenn nicht submitted
            'weiterbildung' => $weiterbildungCardDefinition,
            'playerId' => $this->playerId,
            'answerOptions' => $weiterbildungsOptions,
        ]);

        // wenn submittet anderes Modal rendern 'weiterbildung-result-modal'
        // bekommt WeiterbildungsCardDefinitiion mit, answerOptions, evtl playerId, correctAnswerId (classe korrekt wird grün, ... 2 Fälle), submittedAnswerId
    }
}
