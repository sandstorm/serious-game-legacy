@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')

@props([
    'moneySheet' => null,
    'gameEvents' => null,
    'playerId' => null,
])

<div class="konjunkturphase-summary__money-sheet">
    <table class="konjunkturphase-summary-table">
        <tr class="konjunkturphase-summary-table__total-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__heading-column">Aktueller Kontostand</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->guthabenBeforeKonjunkturphaseChange->formatWithIcon() !!}</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__icon-column">
                <i class="icon-plus text--success" aria-hidden="true"></i>
            </td>
            <td class="konjunkturphase-summary-table__heading-column" colspan="2">Einnahmen</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Finanzanlagen und Vermögenswerte</td>
            <td class="konjunkturphase-summary-table__value-column">{!!$moneySheet->annualIncomeForAllAssets->formatWithIcon()!!}</td>
        </tr>
        <tr class="konjunkturphase-summary-table__bottom-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Gehalt</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->gehalt->formatWithIcon() !!}</td>
        </tr>

        <tr>
            <td class="konjunkturphase-summary-table__icon-column">
                <i class="icon-minus text--danger" aria-hidden="true"></i>
            </td>
            <td class="konjunkturphase-summary-table__heading-column" colspan="2">Ausgaben</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Kredite</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->annualExpensesForAllLoans->formatWithIcon() !!}</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Steuern und Abgaben</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->steuernUndAbgaben->formatWithIcon() !!}</td>
        </tr>
        @if ($moneySheet->insolvenzabgaben->value < 0)
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Insolvenzabgaben</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->insolvenzabgaben->formatWithIcon() !!}</td>
        </tr>
        @endif
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Lebenshaltungskosten</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->lebenshaltungskosten->formatWithIcon() !!}</td>
        </tr>
        <tr class="konjunkturphase-summary-table__bottom-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Versicherungen</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->totalInsuranceCost->formatWithIcon() !!}</td>
        </tr>
        <tr class="konjunkturphase-summary-tabe__total-row">
            <td class="konjunkturphase-summary-table__icon-column">
                <i class="icon-ist-gleich" aria-hidden="true"></i>
            </td>
            <td class="konjunkturphase-summary-table__heading-column">Summe der Ein- und Ausgaben</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->totalFromPlayerInput->formatWithIcon() !!}</td>
        </tr>
        <tr class="konjunkturphase-summary-tabe__total-row">
            <td class="konjunkturphase-summary-table__icon-column">
                <i class="icon-ist-gleich" aria-hidden="true"></i>
            </td>
            <td class="konjunkturphase-summary-table__heading-column">Neuer Kontostand</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->guthabenAfterKonjunkturphaseChange->formatWithIcon() !!}</td>
        </tr>
        @if($moneySheet->guthabenAfterKonjunkturphaseChange->value < 0)
            <tr class="konjunkturphase-summary-tabe__total-row">
                <td class="konjunkturphase-summary-table__icon-column">
                    <i class="icon-insolvent text--danger" aria-hidden="true"></i>
                </td>
                <td class="konjunkturphase-summary-table__heading-column">Dein aktueller Kontostand und deine Einnahmen reichen nicht, um deine Kosten zu decken.</td>
                <td class="konjunkturphase-summary-table__value-column"></td>
            </tr>
            @if($this->canCancelInsurances()->canExecute)
                <tr class="konjunkturphase-summary-tabe__total-row">
                    <td class="konjunkturphase-summary-table__icon-column"></td>
                    <td class="konjunkturphase-summary-table__heading-column">Du hast nicht genügend Geld auf dem Konto, um deine Versicherung zu bezahlen.</td>
                    <td class="konjunkturphase-summary-table__value-column">
                        <button
                            wire:click="cancelAllInsurancesToAvoidInsolvenz()"
                            type="button"
                            @class([
                                "button",
                                "button--type-primary",
                                "button--size-small",
                                $this->getPlayerColorClass(),
                            ])
                        >
                            Versicherungen kündigen
                        </button>
                    </td>
                </tr>
            @endif
            @if(PlayerState::getTotalValueOfAllInvestmentsForPlayer($gameEvents, $playerId)->value > 0)
                <tr class="konjunkturphase-summary-tabe__total-row">
                    <td class="konjunkturphase-summary-table__icon-column"></td>
                    <td class="konjunkturphase-summary-table__heading-column">Du hast nicht genügend Geld auf dem Konto. Verkaufe deine Investitionen, um deine Kosten decken zu können.</td>
                    <td class="konjunkturphase-summary-table__value-column">
                        <button
                            wire:click="toggleSellInvestmentsToAvoidInsolvenzModal()"
                            type="button"
                            @class([
                                "button",
                                "button--type-primary",
                                "button--size-small",
                                $this->getPlayerColorClass(),
                            ])
                        >
                            Investitionen verkaufen
                        </button>
                    </td>
                </tr>
            @endif
        @endif
    </table>
</div>
