@props([
    '$gameEvents' => null,
    '$playerId' => null,
])

@extends ('components.modal.modal', ['closeModal' => "toggleEditExpenses()", 'size' => 'large'])
@section('title')
    Money Sheet - Ausgaben
@endsection

@section('content')
    <x-money-sheet.expenses.money-sheet-expenses :money-sheet="$moneySheet" :game-events="$gameEvents" :player-id="$playerId" />
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="toggleEditExpenses()">Schließen</button>
@endsection
