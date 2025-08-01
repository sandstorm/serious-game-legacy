@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()", 'size' => 'large'])

@props([
    'gameEvents' => null,
    'playerId' => null,
    'moneySheet' => null,
])

@section('icon')
    <button type="button" class="button button--type-icon" wire:click="toggleEditIncome()">
        <i class="icon-lupe-2" aria-hidden="true"></i>
        <span class="sr-only">Zurück zur Moneysheet Übersicht</span>
    </button>
@endsection

@section('title')
    Money Sheet - Einnahmen
@endsection

@section('content')
    <x-money-sheet.income.money-sheet-income :money-sheet="$moneySheet" :player-id="$playerId" :game-events="$gameEvents" />
@endsection

