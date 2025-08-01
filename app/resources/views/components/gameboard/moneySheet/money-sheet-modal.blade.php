@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()", 'size' => 'large'])

@section('icon')
    <i class="icon-lupe-2" aria-hidden="true"></i>
@endsection

@section('title')
    Moneysheet Übersicht
@endsection

@section('content')
    <x-gameboard.moneySheet.money-sheet :money-sheet="$moneySheet"/>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeMoneySheet()">Schließen</button>
@endsection
