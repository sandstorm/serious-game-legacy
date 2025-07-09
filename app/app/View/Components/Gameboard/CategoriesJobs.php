<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Livewire\Dto\GameboardInformationForCategory;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class CategoriesJobs extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $playerId,
        public GameboardInformationForCategory $category
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.categories.categories-jobs', [
            'jobDefinition' => PlayerState::getJobForPlayer($this->gameEvents, $this->playerId),
        ]);
    }

}
