@use('\App\Livewire\ValueObject\ExpensesTabEnum')

@props([
    '$moneySheet' => null,
])


<div class="tabs">
    <ul role="tablist" class="tabs__list">
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::LOANS])>
            <button id="investments" type="button" class="button" role="tab"
                    wire:click="$set('activeTabForExpenses', '{{ ExpensesTabEnum::LOANS }}')">
                Kredite
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::KIDS])>
            <button id="kids" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', '{{ ExpensesTabEnum::KIDS }}')">
                Kinder
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::INSURANCES])>
            <button id="insurances" type="button" class="button" role="tab"
                    wire:click="$set('activeTabForExpenses', '{{ ExpensesTabEnum::INSURANCES }}')">
                Versicherungen
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::TAXES])>
            <button id="taxes" type="button" class="button" role="tab"
                    wire:click="$set('activeTabForExpenses', '{{ ExpensesTabEnum::TAXES }}')">
                Steuern und Abgaben
                @if($moneySheet->doesSteuernUndAbgabenRequirePlayerAction)
                    (!!)
                @endif
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::LIVING_COSTS])>
            <button id="livingCosts" type="button" class="button" role="tab"
                    wire:click="$set('activeTabForExpenses', '{{ ExpensesTabEnum::LIVING_COSTS }}')">
                Lebenshaltungskosten
                @if($moneySheet->doesLebenshaltungskostenRequirePlayerAction)
                    (!!)
                @endif
            </button>
        </li>
    </ul>

    @if ($this->activeTabForExpenses === ExpensesTabEnum::LOANS)
        <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-loans :gameEvents="$gameEvents" :playerId="$playerId"/>
        </div>
    @elseif ($this->activeTabForExpenses === ExpensesTabEnum::KIDS)
        <div aria-labelledby="kids" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-kids :game-events="$gameEvents" :player-id="$playerId"/>
        </div>
    @elseif ($this->activeTabForExpenses === ExpensesTabEnum::INSURANCES)
        <div aria-labelledby="insurances" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-insurances :gameEvents="$gameEvents" :playerId="$playerId"/>
        </div>
    @elseif ($this->activeTabForExpenses === ExpensesTabEnum::TAXES)
        <div aria-labelledby="taxes" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-taxes :money-sheet="$moneySheet"/>
        </div>
    @elseif ($this->activeTabForExpenses === ExpensesTabEnum::LIVING_COSTS)
        <div aria-labelledby="livingCosts" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-living-costs :money-sheet="$moneySheet"/>
        </div>
    @endif
</div>
