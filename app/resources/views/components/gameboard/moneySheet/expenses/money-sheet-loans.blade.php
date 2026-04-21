@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')
@use('Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum')

@props([
    'loans' => null,
    'totalRepaymentValue' => null,
    'sumOfRepaymentsPerRound' => null,
])

<div class="tabs__upper-content">
    @if ($loans)
        <table>
            <thead>
            <tr>
                <th>Kredit</th>
                <th class="text-align--right">Kredithöhe</th>
                <th class="text-align--right">Offene Rückzahlungssumme</th>
                <th class="text-align--right">Rückzahlung pro Runde</th>
                <th class="text-align--right">offene Raten</th>
                <td></td>
            </tr>
            </thead>
            <tbody>
            @foreach($loans as $key => $loan)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td class="text-align--right"><x-money-amount :value="$loan->loanData->loanAmount" /></td>
                    <td class="text-align--right"><x-money-amount :value="MoneySheetState::getOpenRepaymentValueForLoan($gameEvents, $playerId, $loan->loanId)" /></td>
                    <td class="text-align--right"><x-money-amount :value="$loan->loanData->repaymentPerKonjunkturphase" /></td>
                    <td class="text-align--right">{{ MoneySheetState::getOpenRatesForLoan($gameEvents, $playerId, $loan->loanId) }}</td>
                    <td>
                        @if (MoneySheetState::getOpenRatesForLoan($gameEvents, $playerId, $loan->loanId) > 0)
                            <button @class([
                                "button",
                                "button--type-primary",
                                "button--size-small",
                                $this->getPlayerColorClass(),
                            ]) wire:click="showRepayLoan('{{ $loan->loanId->value }}')">
                                Kredit tilgen
                            </button>
                        @endif
                    </td>
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
        <button
            type="button"
            @class([
                "button",
                "button--type-primary",
                $this->getPlayerColorClass(),
                $this->isPlayerAllowedToTakeOutALoan() ? "" : "button--disabled",
            ])
            wire:click="showTakeOutALoan()"
        >
            Kredit aufnehmen
        </button>
        <span>Aktueller Zinssatz: <x-formatted-number :value="KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->value" suffix="%" /></span>
    </div>
    <div class="loans__summary-column">
        <span class="badge-with-background"><x-money-amount :value="$totalRepaymentValue" with-icon /></span>
        <span>Summe Rückzahlungen</span>
    </div>
    <div class="loans__summary-column">
        <span class="badge-with-background"><x-money-amount :value="$sumOfRepaymentsPerRound" with-icon /></span>
        <span>Summe Rückzahlungen pro Runde</span>
    </div>
</div>

