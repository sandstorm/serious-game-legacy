@props(['moneySheet' => null])

<div class="moneysheet">
    <button wire:click="toggleEditIncome()" @class(["moneysheet__income", $this->getPlayerColorClass()])>
        <table>
            <thead>
            <tr>
                <th><h2><i class="icon-plus text--success" aria-hidden="true"></i> Einnahmen</h2></th>
                <th class="text-align--right font-size--xl"><i class="icon-euro" aria-hidden="true"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Finanzanlagen und Vermögenswerte</td>
                <td class="text-align--right">{!! $moneySheet->sumOfAllAssets->formatWithIcon() !!}</td>
            </tr>
            <tr>
                <td>Gehalt</td>
                <td class="text-align--right">{!! $moneySheet->gehalt->formatWithIcon() !!}</td>
            </tr>
            </tbody>
        </table>
    </button>
    <button wire:click="toggleEditExpenses()" @class(["moneysheet__expenses", $this->getPlayerColorClass()])>
        <table>
            <thead>
            <tr>
                <th><h2><i class="icon-minus text--danger" aria-hidden="true"></i> Ausgaben</h2></th>
                <th class="text-align--right font-size--xl"><i class="icon-euro" aria-hidden="true"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Kredite</td>
                <td class="text-align--right">
                    {!! $moneySheet->sumOfAllLoans->formatWithIcon() !!}
                </td>
            </tr>
            <tr>
                <td>Versicherungen</td>
                <td class="text-align--right">
                    {!! $moneySheet->totalInsuranceCost->formatWithIcon() !!}
                </td>
            </tr>
            <tr>
                <td>Steuern und Abgaben</td>
                <td class="text-align--right">
                    {!! $moneySheet->steuernUndAbgaben->formatWithIcon() !!}
                    @if($moneySheet->doesSteuernUndAbgabenRequirePlayerAction)
                        <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Lebenshaltungskosten</td>
                <td class="text-align--right">
                    {!! $moneySheet->lebenshaltungskosten->formatWithIcon() !!}
                    @if($moneySheet->doesLebenshaltungskostenRequirePlayerAction)
                        <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                    @endif
                </td>
            </tr>
            </tbody>
        </table>
    </button>

    <div class="moneysheet__information">
        <table>
            <thead>
            <tr>
                <th class="font-size--xl">
                    <i class="icon-fehler" aria-hidden="true"></i> <span class="sr-only">Information</span>
                </th>
            </tr>
            </thead>
            <tbod>
                <tr>
                    <td>
                        <p>
                            Bei allen Einnahmen und Ausgaben, die Du selbst berechnen musst, hast Du immer zwei Versuche. <br />
                            <strong>Bei dem dritten Fehlversuch hilft Dir das Spiel. Dir werden jedoch 500 € abgezogen.</strong>
                        </p>
                    </td>
                </tr>
            </tbod>
        </table>
    </div>

    <div class="moneysheet__income-sum">
        {!! $moneySheet->annualIncome->formatWithIcon() !!}
    </div>
    <div class="moneysheet__expenses-sum">
        {!! $moneySheet->annualExpensesFilledOutByPlayer->formatWithIcon() !!}
    </div>
    <div class="moneysheet__sum">
        = {!! $moneySheet->totalFilledOutByPlayer->formatWithIcon() !!}
    </div>
</div>
