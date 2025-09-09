@extends ('components.modal.modal', ["closeModal" => "closeMoneySheet()", "type" => "borderless"])

@props([
    'gameEvents' => null,
    'playerId' => null,
    'moneySheet' => null,
])

@section('icon')
    <button type="button" class="button button--type-icon" wire:click="toggleEditIncome()">
        <i class="icon-lupe-2" aria-hidden="true"></i>
        <span class="sr-only">Zurück zur <span lang="en">Moneysheet</span> Übersicht</span>
    </button>
@endsection

@section('content')
    <x-money-sheet.income.money-sheet-income :money-sheet="$moneySheet" :player-id="$playerId" :game-events="$gameEvents" />
@endsection

