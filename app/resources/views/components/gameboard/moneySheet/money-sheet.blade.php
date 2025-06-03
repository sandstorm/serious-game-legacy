@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()"])
@section('title')
    Money Sheet
@endsection

@section('content')
    <div class="moneysheet">
        <div class="moneysheet__income">
            <h2>Einnahmen</h2>
        </div>
        <div class="moneysheet__expenses">
            <h2>Ausgaben</h2>
            <ul>
                <li>
                    <span>Lebenshaltungskosten</span>
                    <span>{{$lebenskosten}}€</span>
                </li>
            </ul>
        </div>
        <div class="moneysheet__income-sum">
            xx.xxx€
        </div>
        <div class="moneysheet__expenses-sum">
            - xx.xxx€
        </div>
        <div class="moneysheet__sum">
            = xx.xxx€
        </div>
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeMoneySheet()">Schließen</button>
@endsection
