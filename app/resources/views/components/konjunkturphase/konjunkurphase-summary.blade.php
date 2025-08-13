@props([
    'moneySheet' => null,
])

<div class="konjunkturphase-summary__money-sheet">
    <table class="konjunkturphase-summary-table">
        <tr>
            <th class="konjunkturphase-summary-table__icon-column">
                <i class="icon-plus text--success" aria-hidden="true"></i>
            </th>
            <th class="konjunkturphase-summary-table__heading-column" colspan="2">Einnahmen</th>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Finanzanlagen und Vermögenswerte</td>
            <td class="konjunkturphase-summary-table__value-column">{!!$moneySheet->sumOfAllAssets->formatWithIcon()!!}</td>
        </tr>
        <tr class="konjunkturphase-summary-table__bottom-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Gehalt</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->gehalt->formatWithIcon() !!}</td>
        </tr>

        <tr>
            <th class="konjunkturphase-summary-table__icon-column">
                <i class="icon-minus text--danger" aria-hidden="true"></i>
            </th>
            <th class="konjunkturphase-summary-table__heading-column" colspan="2">Ausgaben</th>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Kredite</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->sumOfAllLoans->formatWithIcon() !!}</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Lebenshaltungskosten</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->lebenshaltungskosten->formatWithIcon() !!}</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Steuern und Abgaben</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->steuernUndAbgaben->formatWithIcon() !!}</td>
        </tr>
        <tr class="konjunkturphase-summary-table__bottom-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Versicherungen</td>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->totalInsuranceCost->formatWithIcon() !!}</td>
        </tr>
        <tr class="konjunkturphase-summary-tabe__total-row">
            <th class="konjunkturphase-summary-table__icon-column">=</th>
            <th class="konjunkturphase-summary-table__heading-column">Gesamt</th>
            <td class="konjunkturphase-summary-table__value-column">{!! $moneySheet->totalFilledOutByPlayer->formatWithIcon() !!}</td>
        </tr>
    </table>
</div>
