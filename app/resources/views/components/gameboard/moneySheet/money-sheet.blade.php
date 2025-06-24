@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('\App\Livewire\ValueObject\ExpensesTabEnum')
@use('\App\Livewire\ValueObject\IncomeTabEnum')

@extends ('components.modal.modal', ['closeModal' => "closeMoneySheet()", 'size' => 'medium'])
@section('title')
    Money Sheet
@endsection

@section('content')
    <div class="moneysheet">
        <div class="moneysheet__income">
            <h2>Einnahmen</h2>
            <button type="button" class="button button--type-primary button--size-small"
                    wire:click="toggleEditIncome()">Bearbeiten
            </button>
            <table>
                <tbody>
                <tr>
                    <td>Finanzanlagen und Vermögenswerte</td>
                    <td class="text-align--right">0€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showIncomeTab('{{ IncomeTabEnum::INVESTMENTS }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Gehalt</td>
                    <td class="text-align--right">{{$moneySheet->gehalt}}€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showIncomeTab('{{ IncomeTabEnum::SALARY }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="moneysheet__expenses">
            <h2>Ausgaben</h2>
            <button type="button" class="button button--type-primary button--size-small"
                    wire:click="toggleEditExpenses()">Bearbeiten
            </button>
            <table>
                <tbody>
                <tr>
                    <td>Kredite</td>
                    <td class="text-align--right">0€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showExpensesTab('{{ ExpensesTabEnum::LOANS }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Kinder</td>
                    <td class="text-align--right">0€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showExpensesTab('{{ ExpensesTabEnum::KIDS }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Versicherungen</td>
                    <td class="text-align--right">0€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showExpensesTab('{{ ExpensesTabEnum::INSURANCES }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Steuern und Abgaben
                        @if($moneySheet->doesSteuernUndAbgabenRequirePlayerAction)
                            <span><strong>(!!)</strong></span>
                        @endif
                    </td>
                    <td class="text-align--right">{{$moneySheet->steuernUndAbgaben}}€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showExpensesTab('{{ ExpensesTabEnum::TAXES }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Lebenshaltungskosten
                        @if($moneySheet->doesLebenshaltungskostenRequirePlayerAction)
                            <span><strong>(!!)</strong></span>
                        @else <span>mist</span> @endif
                    </td>
                    <td class="text-align--right">{{$moneySheet->lebenshaltungskosten}}€</td>
                    <td>
                        <button type="button" class="button button--type-primary button--size-small"
                                wire:click="showExpensesTab('{{ ExpensesTabEnum::LIVING_COSTS }}')">Bearbeiten
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="moneysheet__income-sum">
            {{$moneySheet->gehalt}}€
        </div>
        <div class="moneysheet__expenses-sum">
            - {{$moneySheet->lebenshaltungskosten + $moneySheet->steuernUndAbgaben}}€
        </div>
        <div class="moneysheet__sum">
            = {{ $moneySheet->gehalt - $moneySheet->lebenshaltungskosten - $moneySheet->steuernUndAbgaben}}€
        </div>
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeMoneySheet()">Schließen</button>
@endsection
