@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\CoreGameLogic\Feature\MoneySheet\State\MoneySheetState')
@use('Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum')

@props([
    '$loans' => null,
    '$repaymentPeriod' => null,
    '$sumOfLoans' => null,
])

@if ($this->takeOutALoanIsVisible)
    <h3>Kredit aufnehmen</h3>

    <form wire:submit="takeOutALoan">
        <div class="take-out-loan">
            <div class="take-out-loan__form">
                ID = {{ $this->takeOutALoanForm->loanId }}
                <div class="form__group">
                    <label for="loanAmount">Kredithöhe</label>
                    <x-form.textfield wire:model="takeOutALoanForm.loanAmount" id="loanAmount" name="loanAmount" type="number" min="1" />
                    <span>
                        @if (PlayerState::getJobForPlayer($gameEvents, $playerId) !== null)
                            Gesamtes Kreditvolumen darf 10-faches der aktuellen Einnahmen + Vermögenswerte nicht übersteigen!
                        @else
                            Gesamtes Kreditvolumen darf 80% der aktuellen Einnahmen + Vermögenswerte nicht übersteigen!
                        @endif
                    </span>
                    @error('takeOutALoanForm.loanAmount') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <div class="form__group">
                    <label for="totalRepayment">Rückzahlungssumme</label>
                    <x-form.textfield wire:model="takeOutALoanForm.totalRepayment" id="totalRepayment" name="totalRepayment" type="number" min="1" step="0.1" />
                    <span>Rückzahlungssumme = Kreditsumme * (1 + Zinssatz/ {{ $repaymentPeriod }}).</span>
                    @error('takeOutALoanForm.totalRepayment') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <div class="form__group">
                    <label for="repaymentPerKonjunkturphase">Rückzahlung pro Runde</label>
                    <x-form.textfield wire:model="takeOutALoanForm.repaymentPerKonjunkturphase" id="repaymentPerKonjunkturphase" name="repaymentPerKonjunkturphase" type="number" min="1" step="0.1" />
                    <span>Der Kredit wird innerhalb von {{ $repaymentPeriod }} Jahren abbezahlt!</span>
                    @error('takeOutALoanForm.repaymentPerKonjunkturphase') <span class="form__error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="take-out-loan__info">
                <p>
                    Aktueller {{ KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->modifier }}% Zins.
                </p>
                <p>
                    Guthaben: {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
                </p>
            </div>

            <div class="take-out-loan__actions">
                @if ($this->takeOutALoanForm->generalError)
                    <span class="form__error">{{ $this->takeOutALoanForm->generalError }}</span>
                @endif
                <button type="button" class="button button--type-outline-primary" wire:click="toggleTakeOutALoan()">
                    Abbrechen
                </button>
                <x-form.submit>Kredit aufnehmen</x-form.submit>
            </div>
        </div>
    </form>
@else
    <h3>Kredite</h3>
    @if ($loans)
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Kredithöhe</th>
            <th>Rückzahlungssumme</th>
            <th>Rückzahlung pro Runde</th>
            <th>offene Raten</th>
        </tr>
        </thead>
        <tbody>
        @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->loanId->value }}</td>
                <td>{!! $loan->loanData->loanAmount->format() !!}</td>
                <td>{!! $loan->loanData->totalRepayment->format() !!}</td>
                <td>{!! $loan->loanData->repaymentPerKonjunkturphase->format() !!}</td>
                <td>{!! MoneySheetState::getOpenRatesForLoan($gameEvents, $playerId, $loan->loanId)->format() !!}</td>
            </tr>
        @endforeach
        <tr>
            <td class="text-align--right">Kredite gesamt</td>
            <td>{!! $sumOfLoans->format() !!}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
    @else
        <div>Du hast keine Kredite aufgenommen.</div>
    @endif

    <button
        type="button" class="button button--type-primary" wire:click="toggleTakeOutALoan()"
    >
        Kredit aufnehmen
    </button>
@endif

