@use('\App\Livewire\ValueObject\ExpensesTabEnum')
@use('\App\Livewire\ValueObject\IncomeTabEnum')

<div class="moneysheet">
    <div @class(["moneysheet__income", $this->getPlayerColorClass()])>
        <table>
            <thead>
            <tr>
                <th wire:click="toggleEditIncome()"><h2><i class="icon-plus text--success" aria-hidden="true"></i> Einnahmen</h2></th>
                <th class="text-align--right font-size--xl"><i class="icon-euro" aria-hidden="true"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td wire:click="showIncomeTab('{{ IncomeTabEnum::INVESTMENTS }}')">Finanzanlagen und Vermögenswerte</td>
                <td class="text-align--right">{!! $moneySheet->sumOfAllStocks->format() !!}</td>
            </tr>
            <tr>
                <td wire:click="showIncomeTab('{{ IncomeTabEnum::SALARY }}')">Gehalt</td>
                <td class="text-align--right">{!! $moneySheet->gehalt->format() !!}</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div @class(["moneysheet__expenses", $this->getPlayerColorClass()])>
        <table>
            <thead>
            <tr>
                <th wire:click="toggleEditExpenses()"><h2><i class="icon-minus text--danger" aria-hidden="true"></i> Ausgaben</h2></th>
                <th class="text-align--right font-size--xl"><i class="icon-euro" aria-hidden="true"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td wire:click="showExpensesTab('{{ ExpensesTabEnum::LOANS }}')">Kredite</td>
                <td class="text-align--right">
                    {!! $moneySheet->sumOfAllLoans->format() !!}
                </td>
            </tr>
            <tr>
                <td wire:click="showExpensesTab('{{ ExpensesTabEnum::KIDS }}')">Kinder</td>
                <td class="text-align--right">
                    0 €
                </td>
            </tr>
            <tr>
                <td wire:click="showExpensesTab('{{ ExpensesTabEnum::INSURANCES }}')">Versicherungen</td>
                <td class="text-align--right">
                    {!! $moneySheet->totalInsuranceCost->format() !!}
                </td>
            </tr>
            <tr>
                <td wire:click="showExpensesTab('{{ ExpensesTabEnum::TAXES }}')">Steuern und Abgaben</td>
                <td class="text-align--right">
                    {!! $moneySheet->steuernUndAbgaben->format() !!}
                    @if($moneySheet->doesSteuernUndAbgabenRequirePlayerAction)
                        <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                    @endif
                </td>
            </tr>
            <tr>
                <td wire:click="showExpensesTab('{{ ExpensesTabEnum::LIVING_COSTS }}')">Lebenshaltungskosten</td>
                <td class="text-align--right">
                    {!! $moneySheet->lebenshaltungskosten->format() !!}
                    @if($moneySheet->doesLebenshaltungskostenRequirePlayerAction)
                        <div class="moneysheet__action-required"><span class="sr-only">Berechnung erforderlich</span></div>
                    @endif
                </td>
            </tr>
            </tbody>
        </table>
    </div>

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
        {!! $moneySheet->annualExpenses->formatWithIcon() !!}
    </div>
    <div class="moneysheet__sum">
        = {!! $moneySheet->total->formatWithIcon() !!}
    </div>
</div>
