@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()"])
@section('title')
    Money Sheet
@endsection

@section('content')
    <div class="moneysheet">
        <div class="moneysheet__income">
            <h2>Einnahmen</h2>
            <table>
                <tbody>
                <tr>
                    <td>Finanzanlagen und Vermögenswerte</td>
                    <td class="text-align--right">0€</td>
                </tr>
                <tr>
                    <td>Gehalt</td>
                    <td class="text-align--right">{{$moneySheet->gehalt}}€</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="moneysheet__expenses">
            <h2>Ausgaben</h2>
            <table>
                <tbody>
                <tr>
                    <td>Verbindlichkeiten</td>
                    <td class="text-align--right">0€</td>
                </tr>
                <tr>
                    <td>Kinder</td>
                    <td class="text-align--right">0€</td>
                </tr>
                <tr>
                    <td>Versicherungen</td>
                    <td class="text-align--right">0€</td>
                </tr>
                <tr>
                    <td>Steuern und Abgaben</td>
                    <td class="text-align--right">0€</td>
                </tr>
                <tr>
                    <td>Lebenshaltungskosten</td>
                    <td class="text-align--right">{{$moneySheet->lebenskosten}}€</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="moneysheet__income-sum">
            {{$moneySheet->gehalt}}€
        </div>
        <div class="moneysheet__expenses-sum">
            - {{$moneySheet->lebenskosten}}€
        </div>
        <div class="moneysheet__sum">
            = {{ PlayerState::getGuthabenForPlayer($this->gameStream(), $playerId) }}€
        </div>
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeMoneySheet()">Schließen</button>
@endsection
