@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')

@props([
    'loans' => [],
])

@if ($this->takeOutALoanIsVisible)
    <h3>Kredit aufnehmen</h3>

    <form wire:submit="takeOutALoan">
        <div class="take-out-loan">
            <div class="take-out-loan__form">
                <div class="form__group">
                    <label for="intendedUse">Verwendungszweck</label>
                    <x-form.textfield wire:model="takeOutALoanForm.intendedUse" id="intendedUse" name="intendedUse" maxlength="255" />
                    @error('takeOutALoanForm.intendedUse') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <div class="form__group">
                    <label for="loanAmount">Kredithöhe</label>
                    <x-form.textfield wire:model="takeOutALoanForm.loanAmount" id="loanAmount" name="loanAmount" type="number" />
                    <span>
                        Gesamtes Kreditvolumen darf 10-faches der aktuellen Einnahmen + Vermögenswerte nicht übersteigen!
                    </span>
                    @error('takeOutALoanForm.loanAmount') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <div class="form__group">
                    <label for="totalRepayment">Rückzahlungssumme</label>
                    <x-form.textfield wire:model="takeOutALoanForm.totalRepayment" id="totalRepayment" name="totalRepayment" type="number" step="0.1" />
                    <span>Rückzahlungssumme = Kreditsumme * (1 + Zinssatz/ 20).</span>
                    @error('takeOutALoanForm.totalRepayment') <span class="form__error">{{ $message }}</span> @enderror
                </div>

                <div class="form__group">
                    <label for="repaymentPerKonjunkturphase">Rückzahlung pro Runde</label>
                    <x-form.textfield wire:model="takeOutALoanForm.repaymentPerKonjunkturphase" id="repaymentPerKonjunkturphase" name="repaymentPerKonjunkturphase" type="number" step="0.1" />
                    <span>Der Kredit wird innerhalb von 20 Jahren abbezahlt!</span>
                    @error('takeOutALoanForm.repaymentPerKonjunkturphase') <span class="form__error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="take-out-loan__info">
                <p>
                    Aktueller {{ KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->leitzins }}% Zins.
                </p>
                <p>
                    Guthaben: {{ number_format(PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->value, 2, ',', '.') }} €
                </p>

            </div>

            <div class="take-out-loan__actions">
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
            <th>Kreditverwendung</th>
            <th>Kredithöhe</th>
            <th>Rückzahlungssumme</th>
            <th>Rückzahlung pro Runde</th>
            <th>offene Raten</th>
        </tr>
        </thead>
        <tbody>
        @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->intendedUse }}</td>
                <td>{{ number_format($loan->loanAmount->value, 2, ',', '.') }} €</td>
                <td>{{ number_format($loan->totalRepayment->value, 2, ',', '.') }} €</td>
                <td>{{ number_format($loan->repaymentPerKonjunkturphase->value, 2, ',', '.') }} €</td>
                <td>xxx €</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" class="text-align--right">Kredite gesamt</td>
            <td>0€</td>
        </tr>
        </tbody>
    </table>
    @else
        <div>Du hast keine Kredite aufgenommen.</div>
    @endif

    <button
        {{ PlayerState::getJobForPlayer($gameEvents, $playerId) === null ? 'disabled' : '' }}
        type="button" class="button button--type-primary" wire:click="toggleTakeOutALoan()"
    >
        Kredit aufnehmen
    </button>

    @if (PlayerState::getJobForPlayer($gameEvents, $playerId) === null)
        <div>Kredit aufnehmen ist nur möglich, wenn du einen Job hast!</div>
    @endif
@endif

