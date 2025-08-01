@use('\App\Livewire\ValueObject\IncomeTabEnum')

@props([
    '$jobDefinition' => null,
    '$gameEvents' => null,
    '$playerId' => null,
])

<div class="tabs">
    <ul role="tablist" class="tabs__list">
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForIncome === IncomeTabEnum::INVESTMENTS])>
            <button id="investments" type="button" class="button button--type-borderless" role="tab" wire:click="showIncomeTab('{{ IncomeTabEnum::INVESTMENTS }}')">
                Finanzanlagen und Vermögenswerte
            </button>
        </li>
        <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForIncome === IncomeTabEnum::SALARY])>
            <button id="salary" type="button" class="button button--type-borderless" role="tab" wire:click="showIncomeTab('{{ IncomeTabEnum::SALARY }}')">
                Gehalt
            </button>
        </li>
    </ul>

    @if ($this->activeTabForIncome === IncomeTabEnum::INVESTMENTS)
        <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
            <x-money-sheet.income.money-sheet-investments :gameEvents="$gameEvents" :playerId="$playerId" />
        </div>
    @elseif ($this->activeTabForIncome === IncomeTabEnum::SALARY)
        <div aria-labelledby="salary" role="tabpanel" class="tabs__tab">
            <x-money-sheet.income.money-sheet-salary :gameEvents="$gameEvents" :playerId="$playerId" />
        </div>
    @endif
</div>
