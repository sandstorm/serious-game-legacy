@props([
    '$moneySheet' => null,
])


<div class="tabs">
    <ul role="tablist" class="tabs__list">
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'credits'])>
            <button id="investments" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'credits')">
                Kredite
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'kids'])>
            <button id="kids" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'kids')">
                Kinder
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'insurances'])>
            <button id="insurances" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'insurances')">
                Versicherungen
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'taxes'])>
            <button id="taxes" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'taxes')">
                Steuern und Abgaben
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === 'livingCosts'])>
            <button id="livingCosts" type="button" class="button" role="tab" wire:click="$set('activeTabForExpenses', 'livingCosts')">
                Lebenshaltungskosten
            </button>
        </li>
    </ul>

    @if ($this->activeTabForExpenses === 'credits')
        <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-credits :gameStream="$gameStream" :playerId="$playerId" />
        </div>
    @elseif ($this->activeTabForExpenses === 'kids')
        <div aria-labelledby="kids" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-kids :game-stream="$gameStream" :player-id="$playerId" />
        </div>
    @elseif ($this->activeTabForExpenses === 'insurances')
        <div aria-labelledby="insurances" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-insurances :gameStream="$gameStream" :playerId="$playerId" />
        </div>
    @elseif ($this->activeTabForExpenses === 'taxes')
        <div aria-labelledby="taxes" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-taxes :gameStream="$gameStream" :playerId="$playerId" />
        </div>
    @elseif ($this->activeTabForExpenses === 'livingCosts')
        <div aria-labelledby="livingCosts" role="tabpanel" class="tabs__tab">
            <x-money-sheet.expenses.money-sheet-living-costs :gameStream="$gameStream" :playerId="$playerId" />
        </div>
    @endif
</div>
