@extends ('components.modal.modal', ['type' => "borderless"])

@use('\App\Livewire\ValueObject\ExpensesTabEnum')

@props([
    'playerId' => null,
    'gameEvents' => null,
    'moneySheet' => null,
])

@section('icon')
    <i class="icon-phasenwechsel" aria-hidden="true"></i> Moneysheet ausfüllen
@endsection

@section('content')
    <div class="tabs">
        <ul role="tablist" class="tabs__list">
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::TAXES])>
                <button id="taxes" type="button" class="button button--type-borderless" role="tab"
                        wire:click="showExpensesTab('{{ ExpensesTabEnum::TAXES }}')">
                    Steuern und Abgaben
                    @if($moneySheet->doesSteuernUndAbgabenRequirePlayerAction)
                        <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                    @endif
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->activeTabForExpenses === ExpensesTabEnum::LIVING_COSTS])>
                <button id="livingCosts" type="button" class="button button--type-borderless" role="tab"
                        wire:click="showExpensesTab('{{ ExpensesTabEnum::LIVING_COSTS }}')">
                    Lebenshaltungskosten
                    @if($moneySheet->doesLebenshaltungskostenRequirePlayerAction)
                        <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                    @endif
                </button>
            </li>
        </ul>

        @if ($this->activeTabForExpenses === ExpensesTabEnum::TAXES)
            <x-money-sheet.expenses.money-sheet-taxes :money-sheet="$moneySheet"/>
        @elseif ($this->activeTabForExpenses === ExpensesTabEnum::LIVING_COSTS)
            <x-money-sheet.expenses.money-sheet-living-costs :game-events="$gameEvents" :player-id="$playerId" :money-sheet="$moneySheet"/>
        @endif
    </div>
@endsection

@section('footer')
    <button
        type="button"
        @class([
            "button",
            "button--type-primary",
            "button--disabled" => !$this->canCompleteMoneysheet(),
            $this->getPlayerColorClass(),
        ])
        wire:click="completeMoneysheetForPlayer()"
    >
        Weiter
    </button>
@endsection
