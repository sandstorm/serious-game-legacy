<div class="konjunkturphase-summary__money-sheet">
    <table class="konjunkturphase-summary-table">
        <tr>
            <th class="konjunkturphase-summary-table__icon-column">
                +
            </th>
            <th class="konjunkturphase-summary-table__heading-column" colspan="2">Einnahmen</th>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Finanzanlagen und Vermögenswerte</td>
            <td class="konjunkturphase-summary-table__value-column">0 €</td>
        </tr>
        <tr class="konjunkturphase-summary-table__bottom-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Gehalt (TODO Jobtitel)</td>
            <td class="konjunkturphase-summary-table__value-column">{{$moneySheet->gehalt}} €</td>
        </tr>

        <tr>
            <th class="konjunkturphase-summary-table__icon-column">
                -
            </th>
            <th class="konjunkturphase-summary-table__heading-column" colspan="2">Ausgaben</th>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Kredite</td>
            <td class="konjunkturphase-summary-table__value-column">0 €</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Lebenshaltungskosten</td>
            <td class="konjunkturphase-summary-table__value-column">{{$moneySheet->lebenshaltungskosten}} €</td>
        </tr>
        <tr>
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Steuern und Abgaben</td>
            <td class="konjunkturphase-summary-table__value-column">{{$moneySheet->steuernUndAbgaben}} €</td>
        </tr>
        <tr class="konjunkturphase-summary-table__bottom-row">
            <td class="konjunkturphase-summary-table__empty-column"></td>
            <td class="konjunkturphase-summary-table__name-column">Versicherungen</td>
            <td class="konjunkturphase-summary-table__value-column">0 €</td>
        </tr>
        <tr class="konjunkturphase-summary-tabe__total-row">
            <th class="konjunkturphase-summary-table__icon-column">=</th>
            <th class="konjunkturphase-summary-table__heading-column">Gesamt</th>
            <td class="konjunkturphase-summary-table__value-column">{{$moneySheet->total}}</td>
        </tr>
    </table>
</div>
