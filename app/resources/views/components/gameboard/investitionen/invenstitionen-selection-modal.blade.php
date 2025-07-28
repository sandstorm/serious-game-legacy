@extends ('components.modal.modal', ['closeModal' => "toggleInvestitionenSelectionModal()", 'size' => 'medium'])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    Investitionen
@endsection

@section('content')
    <div class="investitionen-overview">
        <button class="card" wire:click="toggleStocksModal()">
            <h4 class="card__title">Aktien</h4>
            <div class="card__content">
                <i class="icon-aktien" aria-hidden="true"></i>
            </div>
        </button>
        <button class="card card--disabled">
            <h4 class="card__title">ETF</h4>
            <div class="card__content">
                <i class="icon-ETF" aria-hidden="true"></i>
            </div>
        </button>
        <button class="card card--disabled">
            <h4 class="card__title">Krypto</h4>
            <div class="card__content">
                <i class="icon-krypto" aria-hidden="true"></i>
            </div>
        </button>
        <button class="card card--disabled">
            <h4 class="card__title">Immobilien</h4>
            <div class="card__content">
                <i class="icon-immobilien" aria-hidden="true"></i>
            </div>
        </button>
    </div>
@endsection
