@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')
@use('Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum')

@props([
    'loans' => null,
    'sumOfLoans' => null,
    'sumOfRepaymentsPerRound' => null,
])

<div class="tabs__upper-content">
    @if ($loans)
        <table>
            <thead>
            <tr>
                <th>Kredit</th>
                <th class="text-align--right">Kredithöhe</th>
                <th class="text-align--right">Rückzahlungssumme</th>
                <th class="text-align--right">Rückzahlung pro Runde</th>
                <th class="text-align--right">offene Raten</th>
            </tr>
            </thead>
            <tbody>
            @foreach($loans as $key => $loan)
                <tr>
                    <td>{{ $key }}</td>
                    <td class="text-align--right">{!! $loan->loanData->loanAmount->format() !!}</td>
                    <td class="text-align--right">{!! $loan->loanData->totalRepayment->format() !!}</td>
                    <td class="text-align--right">{!! $loan->loanData->repaymentPerKonjunkturphase->format() !!}</td>
                    <td class="text-align--right">{{ MoneySheetState::getOpenRatesForLoan($gameEvents, $playerId, $loan->loanId) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <h4>Du hast aktuell keine Kredite aufgenommen.</h4>
    @endif
</div>

<div class="tabs__lower-content loans__summary">
    <div class="loans__summary-column">
        <button type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass(),
            ])
            wire:click="showTakeOutALoan()"
        >
            Kredit aufnehmen
        </button>
        <span>Aktueller Zinssatz: {{ KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->modifier }}%</span>
    </div>
    <div class="loans__summary-column">
        {!! $sumOfLoans->formatWithIcon() !!}
        <span>Summe Rückzahlungen</span>
    </div>
    <div class="loans__summary-column">
        {!! $sumOfRepaymentsPerRound->formatWithIcon() !!}
        <span>Summe Rückzahlungen pro Runde</span>
    </div>
</div>

