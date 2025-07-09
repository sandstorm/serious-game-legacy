@use('\App\Livewire\ValueObject\InvestitionenTabEnum')

@extends ('components.modal.modal', ['closeModal' => "toggleInvestitionen()", 'size' => 'large'])

@props([
    'gameEvents' => null,
    '$playerId' => null,
])

@section('title')
    Invenstieren
@endsection

@section('content')
    <div class="tabs">
        <ul role="tablist" class="tabs__list">
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->investitionenActiveTab === InvestitionenTabEnum::STOCKS])>
                <button id="investments" type="button" class="button" role="tab" wire:click="$set('investitionenActiveTab', '{{ InvestitionenTabEnum::STOCKS }}')">
                    Aktien
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->investitionenActiveTab === InvestitionenTabEnum::ETF])>
                <button id="salary" type="button" class="button" role="tab" wire:click="$set('investitionenActiveTab', '{{ InvestitionenTabEnum::ETF }}')">
                    ETF
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->investitionenActiveTab === InvestitionenTabEnum::IMMOBILIEN])>
                <button id="crypto" type="button" class="button" role="tab" wire:click="$set('investitionenActiveTab', '{{ InvestitionenTabEnum::IMMOBILIEN }}')">
                    Immobilien
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->investitionenActiveTab === InvestitionenTabEnum::EDELMETALLE])>
                <button id="crypto" type="button" class="button" role="tab" wire:click="$set('investitionenActiveTab', '{{ InvestitionenTabEnum::EDELMETALLE }}')">
                    Edelmetalle
                </button>
            </li>
            <li @class(['tabs__list-item', 'tabs__list-item--active' => $this->investitionenActiveTab === InvestitionenTabEnum::KRYTPO])>
                <button id="crypto" type="button" class="button" role="tab" wire:click="$set('investitionenActiveTab', '{{ InvestitionenTabEnum::KRYTPO }}')">
                    Krypto
                </button>
            </li>
        </ul>

        @if ($this->investitionenActiveTab === InvestitionenTabEnum::STOCKS)
            <div aria-labelledby="investments" role="tabpanel" class="tabs__tab">
                <x-gameboard.investitionen.invenstitionen-stocks :game-events="$gameEvents" :player-id="$playerId" />
            </div>
        @elseif ($this->investitionenActiveTab === InvestitionenTabEnum::ETF)
            <div aria-labelledby="salary" role="tabpanel" class="tabs__tab">
                ETF
            </div>
        @elseif ($this->investitionenActiveTab === InvestitionenTabEnum::IMMOBILIEN)
            <div aria-labelledby="crypto" role="tabpanel" class="tabs__tab">
                Immobilien
            </div>
        @elseif ($this->investitionenActiveTab === InvestitionenTabEnum::EDELMETALLE)
            <div aria-labelledby="crypto" role="tabpanel" class="tabs__tab">
                Edelmetalle
            </div>
        @elseif ($this->investitionenActiveTab === InvestitionenTabEnum::KRYTPO)
            <div aria-labelledby="crypto" role="tabpanel" class="tabs__tab">
                Krypto
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="toggleInvestitionen()">Schließen</button>
@endsection
