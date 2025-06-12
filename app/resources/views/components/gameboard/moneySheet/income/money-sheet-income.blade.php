@props([
    '$jobDefinition' => null,
    '$gameStream' => null,
    '$playerId' => null,
])

<div class="tabs">
    <ul role="tablist" class="tabs__list">
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForIncome === 'investments'])>
            <button id="investments" type="button" class="button" role="tab" wire:click="$set('activeTabForIncome', 'investments')">
                Finanzanlagen und Vermögenswerte
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForIncome === 'salary'])>
            <button id="salary" type="button" class="button" role="tab" wire:click="$set('activeTabForIncome', 'salary')">
                Gehalt
            </button>
        </li>
    </ul>

    @if ($this->activeTabForIncome === 'investments')
        <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
            <x-money-sheet.income.money-sheet-investments :gameStream="$gameStream" :playerId="$playerId" />
        </div>
    @elseif ($this->activeTabForIncome === 'salary')
        <div aria-labelledby="salary" role="tabpanel" class="tabs__tab">
            <x-money-sheet.income.money-sheet-salary :gameStream="$gameStream" :playerId="$playerId" />
        </div>
    @endif
</div>
