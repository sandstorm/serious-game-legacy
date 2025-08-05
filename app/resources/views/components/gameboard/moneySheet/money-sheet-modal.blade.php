@extends ('components.modal.modal', ["closeModal" => "closeMoneySheet()", "type" => "borderless"])

@section('icon')
    <i class="icon-lupe-2" aria-hidden="true"></i> Moneysheet Übersicht
@endsection

@section('content')
    <x-gameboard.moneySheet.money-sheet :money-sheet="$moneySheet"/>
@endsection
