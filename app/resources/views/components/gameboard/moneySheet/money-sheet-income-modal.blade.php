@props([
    '$moneySheet' => null,
    '$gameEvents' => null,
    '$playerId' => null,
])

@extends ('components.modal.modal', ['closeModal' => "toggleEditIncome()", 'size' => 'large'])
@section('title')
    Money Sheet - Einnahmen
@endsection

@section('content')
    <x-money-sheet.income.money-sheet-income :money-sheet="$moneySheet" :player-id="$playerId" :game-events="$gameEvents" />
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="toggleEditIncome()">Schließen</button>
@endsection
