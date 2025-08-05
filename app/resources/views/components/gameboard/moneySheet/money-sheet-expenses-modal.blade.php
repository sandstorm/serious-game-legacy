@extends ('components.modal.modal', ["closeModal" => "closeMoneySheet()", "type" => "borderless"])

@props([
    'gameEvents' => null,
    'playerId' => null,
    'moneySheet' => null,
])

@section('icon')
    <button type="button" class="button button--type-icon" wire:click="toggleEditExpenses()">
        <i class="icon-lupe-2" aria-hidden="true"></i>
        <span class="sr-only">Zurück zur Moneysheet Übersicht</span>
    </button>
@endsection

@section('content')
    <x-money-sheet.expenses.money-sheet-expenses :money-sheet="$moneySheet" :game-events="$gameEvents" :player-id="$playerId" />
@endsection

