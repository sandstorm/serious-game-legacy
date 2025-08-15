<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\Modifier;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetLivingCosts extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
        public MoneySheetDto $moneySheet,
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
                ->getActiveModifiersForHook(HookEnum::LEBENSHALTUNGSKOSTEN_MULTIPLIER, $this->gameEvents)
                ->getModifiers()
        );
        $livingCostMultiplier = MoneySheetState::getMultiplierForLebenshaltungskostenForPlayer($this->gameEvents, $this->playerId);
        $livingCostPercent = $livingCostMultiplier * 100;
        $livingCostMinValue = MoneySheetState::getMinimumValueForLebenshaltungskostenForPlayer($this->gameEvents, $this->playerId);
        return view('components.gameboard.moneySheet.expenses.money-sheet-living-costs', [
            'moneySheet' => $this->moneySheet,
            'modifiers' => $modifierDescriptions,
            'livingCostPercent' => $livingCostPercent,
            'livingCostMinValue' => $livingCostMinValue,
        ]);
    }

}
