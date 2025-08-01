<?php
declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\AnswerForWeiterbildungWasSubmitted;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

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
    public function render(): View
    {
        $weiterbildungCardDefinition = PlayerState::getLastWeiterbildungCardDefinitionForPlayer(
            $this->gameEvents,
            $this->playerId
        );

        $weiterbildungsOptions = PlayerState::getLastWeiterbildungsEventForPlayerThisTurn(
            $this->gameEvents,
            $this->playerId
        )?->shuffeldAnswerOptions;

        $submittedAnswerEvent = null;
        if ($weiterbildungCardDefinition !== null) {
            $submittedAnswerEvent = PlayerState::getSubmittedAnswerForLatestWeiterbildungThisTurn(
                $this->gameEvents,
                $this->playerId
            );
        }

        return view('components.gameboard.weiterbildung.weiterbildung-modal', [
            'weiterbildung' => $weiterbildungCardDefinition,
            'answerOptions' => $weiterbildungsOptions,
            'correctAnswerId' => $weiterbildungCardDefinition?->getCorrectAnswerId(),
            'selectedAnswerId' => $submittedAnswerEvent?->selectedAnswerId,
            'isAnswerCorrect' => $submittedAnswerEvent?->wasCorrect,
        ]);
    }
}
