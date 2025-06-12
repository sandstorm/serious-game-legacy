<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetIncome extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameStream,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $currentJob = PlayerState::getJobForPlayer($this->gameStream, $this->playerId);
        /** @var JobCardDefinition $jobCard */
        $jobCard = $currentJob !== null ? CardFinder::getInstance()->getCardById($currentJob->job) : null;

        return view('components.gameboard.moneySheet.money-sheet-income', [
            'jobDefinition' => $jobCard,
        ]);
    }

}
