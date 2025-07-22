@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()", 'size' => 'medium'])
@section('title')
    Money Sheet
@endsection

@section('content')
    <x-gameboard.moneySheet.money-sheet :money-sheet="$moneySheet"/>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeMoneySheet()">Schließen</button>
@endsection
