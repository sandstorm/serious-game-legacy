@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')
@use('Domain\Definitions\Configuration\Configuration')

@props(['moneySheet' => null])

<div class="moneysheet">
    <button wire:click="toggleEditIncome()" @class(["moneysheet__income", $this->getPlayerColorClass()]) aria-label="Einnahmen bearbeiten">
        <div class="kompetenzen-overview__action-required">
            <i class="icon-pencil" aria-hidden="true"></i>
        </div>
        <table>
            <thead>
            <tr>
                <th><h2><i class="icon-plus text--success" aria-hidden="true"></i> Einnahmen p.a.</h2></th>
                <th class="text-align--right font-size--xl"><span class="sr-only">€</span><i class="icon-euro" aria-hidden="true"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Finanzanlagen und Vermögenswerte</td>
                <td class="text-align--right">{!! $moneySheet->annualIncomeForAllAssets->formatWithIcon() !!}</td>
            </tr>
            <tr>
                <td>Gehalt</td>
                <td class="text-align--right">{!! $moneySheet->gehalt->formatWithIcon() !!}</td>
            </tr>
            </tbody>
        </table>
    </button>
    <button wire:click="toggleEditExpenses()" @class(["moneysheet__expenses", $this->getPlayerColorClass()]) aria-label="Ausgaben bearbeiten">
        <div @class([
                'kompetenzen-overview__action-required',
                MoneySheetState::doesMoneySheetRequirePlayerAction($this->getGameEvents(), $this->myself) ? 'kompetenzen-overview__action-required--active' : ''
            ])
        >
            <i class="icon-pencil" aria-hidden="true"></i>
            @if (MoneySheetState::doesMoneySheetRequirePlayerAction($this->getGameEvents(), $this->myself))
                <span class="sr-only">Berechnung erforderlich</span>
            @endif
        </div>
        <table>
            <thead>
            <tr>
                <th><h2><i class="icon-minus text--danger" aria-hidden="true"></i> Ausgaben p.a.</h2></th>
                <th class="text-align--right font-size--xl"><span class="sr-only">€</span><i class="icon-euro" aria-hidden="true"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Kredite</td>
                <td class="text-align--right">
                    {!! $moneySheet->annualExpensesForAllLoans->formatWithIcon() !!}
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
        <div class="text-align--center font-size--sm">Dein Kontostand</div>
        <div class="badge-with-background font-size--lg">
            {!! PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->myself)->format() !!}
        </div>
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
                            <strong>Bei dem dritten Fehlversuch hilft Dir das Spiel. Dir werden jedoch {{ Configuration::FINE_VALUE }} € abgezogen.</strong>
                        </p>
                    </td>
                </tr>
            </tbod>
        </table>
    </div>

    <div class="moneysheet__income-sum">
        <span class="badge-with-background">{!! $moneySheet->annualIncome->formatWithIcon() !!}</span>
    </div>
    <div class="moneysheet__expenses-sum">
        <span class="badge-with-background">{!! $moneySheet->annualExpensesFromPlayerInput->formatWithIcon() !!}</span>
    </div>
    <div class="moneysheet__sum">
        <span class="badge-with-background">= {!! $moneySheet->totalFromPlayerInput->formatWithIcon() !!}</span>
    </div>
</div>
