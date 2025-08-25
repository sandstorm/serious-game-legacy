@props([
    'category' => null,
    'gameEvents' => null,
    'playerId' => null,
])

<div class="card-pile">
    <button
        class="card"
        aria-label="Investitionen anschauen"
        wire:click="toggleInvestitionenSelectionModal()"
    >
        <div class="card__icon">
            <i class="icon-dots" aria-hidden="true"></i>
        </div>
        <h4 class="card__title">Investitionen</h4>
        <div class="card__content card__content--investitionen">
            <i class="icon-aktien" aria-hidden="true"></i>
            <i class="icon-ETF" aria-hidden="true"></i>
            <i class="icon-immobilien" aria-hidden="true"></i>
            <i class="icon-krypto" aria-hidden="true"></i>
        </div>
    </button>
</div>

@if ($this->showInvestitionenSelelectionModal)
    <x-gameboard.investitionen.investitionen-selection-modal />
@elseif ($this->showStocksModal)
    <x-gameboard.investitionen.investitionen-stocks-modal :game-events="$gameEvents" />
@elseif ($this->showETFModal)
    <x-gameboard.investitionen.investitionen-etf-modal :game-events="$gameEvents" />
@elseif ($this->showCryptoModal)
    <x-gameboard.investitionen.investitionen-crypto-modal :game-events="$gameEvents" />
@endif
