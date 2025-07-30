<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Income;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\Modifier;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetSalary extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $modifiersForPlayer = ModifierCalculator::forStream($this->gameEvents)->forPlayer($this->playerId);
        $modifierDescriptions = array_map(
            fn(Modifier $modifier) => $modifier->description,
            $modifiersForPlayer
                ->getModifiersForHook(HookEnum::ZEITSTEINE)
                ->withAdditional($modifiersForPlayer->getModifiersForHook(HookEnum::GEHALT))
                ->getModifiers()
        );
        return view('components.gameboard.moneySheet.income.money-sheet-salary', [
            'jobDefinition' => PlayerState::getJobForPlayer($this->gameEvents, $this->playerId),
            'gehalt' => PlayerState::getCurrentGehaltForPlayer($this->gameEvents, $this->playerId),
            'gameEvents' => $this->gameEvents,
            'playerId' => $this->playerId,
            'modifiers' => $modifierDescriptions,
        ]);
    }

}
