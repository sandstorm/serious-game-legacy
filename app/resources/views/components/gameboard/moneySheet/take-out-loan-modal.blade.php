@extends ('components.modal.modal', ["closeModal" => "closeTakeOutALoan()", "type" => "borderless"])

@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum')
@use('Domain\Definitions\Configuration\Configuration')

@props([
    'gameEvents' => null,
    'playerId' => null,
])

@section('icon')
    Kredit aufnehmen
@endsection

@section('content')
    <form class="take-out-loan" wire:submit="takeOutALoan">
        <div class="take-out-loan__info-box">
            <i class="icon-info-2" aria-hidden="true"></i>
            <div class="take-out-loan__info-section">
                <small>Dein Kontostand</small>
                {!! PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->format() !!}
            </div>
            <div class="take-out-loan__info-section">
                <small>Deine Vermögenswerte</small>
                {!! PlayerState::getTotalValueOfAllAssetsForPlayer($gameEvents, $playerId)->format() !!}
            </div>
        </div>
        <div class="form__group take-out-loan__amount">
            <label for="loanAmount">Kredithöhe</label>
            <x-form.textfield wire:model="takeOutALoanForm.loanAmount" id="loanAmount" name="loanAmount" type="number" min="1" />
            @error('takeOutALoanForm.loanAmount') <span class="form__error">{{ $message }}</span> @enderror
            <p>
                @if (PlayerState::getJobForPlayer($gameEvents, $playerId) !== null)
                    Das gesamte Kreditvolumen darf das <strong>10-fache</strong> der aktuellen Einnahmen + Vermögenswerte nicht übersteigen! <br />
                @else
                    Gesamtes Kreditvolumen darf <strong>80%</strong> der aktuellen Einnahmen + Vermögenswerte nicht übersteigen!
                @endif
            </p>
        </div>

        <div class="form__group take-out-loan__sum">
            <label for="totalRepayment">Rückzahlungssumme</label>
            <x-form.textfield wire:model="takeOutALoanForm.totalRepayment" id="totalRepayment" name="totalRepayment" type="number" min="1" step="0.01" />
            @error('takeOutALoanForm.totalRepayment') <span class="form__error">{{ $message }}</span> @enderror
            <p>
                Rückzahlungssumme = <br />
                <strong>Kreditsumme * (1 + Zinssatz / {{ Configuration::REPAYMENT_PERIOD }}).</strong><br />
                <strong>Aktueller Zinssatz: {{ KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->value }}%</strong>
            </p>
        </div>

        <div class="form__group take-out-loan__repayment">
            <label for="repaymentPerKonjunkturphase">Rückzahlung pro Runde</label>
            <x-form.textfield wire:model="takeOutALoanForm.repaymentPerKonjunkturphase" id="repaymentPerKonjunkturphase" name="repaymentPerKonjunkturphase" type="number" min="1" step="0.01" />
            @error('takeOutALoanForm.repaymentPerKonjunkturphase') <span class="form__error">{{ $message }}</span> @enderror
            <p>
                Der Kredit wird innerhalb von <strong>{{ Configuration::REPAYMENT_PERIOD }}</strong> Jahren abbezahlt!
            </p>
        </div>

        <div class="take-out-loan__actions">
            @if ($this->takeOutALoanForm->generalError)
                <span class="form__error">{{ $this->takeOutALoanForm->generalError }}</span>
            @endif
            <button type="button" class="button button--type-outline-primary" wire:click="closeTakeOutALoan()">
                Abbrechen
            </button>
            <x-form.submit>Kredit jetzt aufnehmen</x-form.submit>
        </div>
    </form>
@endsection
