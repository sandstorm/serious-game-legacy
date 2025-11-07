@extends ('components.modal.modal', ["closeModal" => "closeTakeOutALoan()", "type" => "borderless"])

@use('Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState')
@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')
@use('Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum')
@use('Domain\Definitions\Configuration\Configuration')
@use('Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator')
@use('Domain\Definitions\Card\ValueObject\MoneyAmount')

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
                <small>Dein Gehalt</small>
                <span class="badge-with-background">
                    {!! new MoneyAmount($this->takeOutALoanForm->salary)->format() !!}
                </span>
            </div>
            <div class="take-out-loan__info-section">
                <small>Deine Vermögenswerte</small>
                <span class="badge-with-background">
                    {!! new MoneyAmount($this->takeOutALoanForm->sumOfAllAssets)->format() !!}
                </span>
            </div>
            <div class="take-out-loan__info-section">
                <small>Deine Verbindlichkeiten</small>
                <span class="badge-with-background">
                    {!! new MoneyAmount($this->takeOutALoanForm->obligations)->format() !!}
                </span>
            </div>
        </div>
        <div class="form-group take-out-loan__amount">
            <label for="loanAmount">Kredithöhe</label>
            <x-form.textfield wire:model="takeOutALoanForm.loanAmount" id="loanAmount" name="loanAmount" type="number" min="1" />
            @error('takeOutALoanForm.loanAmount') <span class="form-error">{{ $message }}</span> @enderror
            <p>
                <strong>Kreditlimit: </strong> {!! LoanCalculator::getMaxLoanAmount($this->takeOutALoanForm->sumOfAllAssets, $this->takeOutALoanForm->salary, $this->takeOutALoanForm->obligations, $this->takeOutALoanForm->wasPlayerInsolventInThePast)->format() !!}
                <br />
                @if (PlayerState::getJobForPlayer($gameEvents, $playerId) !== null)
                    @if (PlayerState::wasPlayerInsolventInThePast($gameEvents, $playerId))
                        Das gesamte Kreditvolumen darf das <strong>2-fache</strong> des aktuellen Jahresgehalt (brutto)
                        <strong>zzgl. Vermögenswerte</strong>, <strong>abzgl. Verbindlichkeiten</strong> nicht übersteigen!
                    @else
                        Das gesamte Kreditvolumen darf das <strong>5-fache</strong> des aktuellen Jahresgehalt (brutto)
                        <strong>zzgl. Vermögenswerte</strong>, <strong>abzgl. Verbindlichkeiten</strong> nicht übersteigen!
                    @endif
                @else
                    @if (PlayerState::wasPlayerInsolventInThePast($gameEvents, $playerId))
                        Gesamtes Kreditvolumen darf <strong>50%</strong> deiner Vermögenswerte
                        <strong>abzgl. Verbindlichkeiten</strong> nicht übersteigen!
                    @else
                        Gesamtes Kreditvolumen darf <strong>80%</strong> deiner Vermögenswerte
                        <strong>abzgl. Verbindlichkeiten</strong> nicht übersteigen!
                    @endif

                @endif
            </p>
        </div>

        <div class="form-group take-out-loan__sum">
            <label for="totalRepayment">Rückzahlungssumme</label>
            <x-form.textfield wire:model="takeOutALoanForm.totalRepayment" id="totalRepayment" name="totalRepayment" type="number" min="1" step="0.01" />
            @error('takeOutALoanForm.totalRepayment') <span class="form-error">{{ $message }}</span> @enderror
            <p>
                Rückzahlungssumme = <br />
                <strong>Kreditsumme * (1 + Zinssatz / {{ Configuration::REPAYMENT_PERIOD }}).</strong><br />
                <strong>Aktueller Zinssatz: {{ KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->value }}%</strong>
            </p>
        </div>

        <div class="form-group take-out-loan__repayment">
            <label for="repaymentPerKonjunkturphase">Rückzahlung pro Runde</label>
            <x-form.textfield wire:model="takeOutALoanForm.repaymentPerKonjunkturphase" id="repaymentPerKonjunkturphase" name="repaymentPerKonjunkturphase" type="number" min="1" step="0.01" />
            @error('takeOutALoanForm.repaymentPerKonjunkturphase') <span class="form-error">{{ $message }}</span> @enderror
            <p>
                Der Kredit wird innerhalb von <strong>{{ Configuration::REPAYMENT_PERIOD }}</strong> Jahren abbezahlt!
            </p>
        </div>

        <div class="take-out-loan__actions">
            @if ($this->takeOutALoanForm->generalError)
                <span class="form-error">{{ $this->takeOutALoanForm->generalError }}</span>
            @endif
            <button type="button" class="button button--type-outline-primary" wire:click="closeTakeOutALoan()">
                Abbrechen
            </button>
            <x-form.submit>Kredit jetzt aufnehmen</x-form.submit>
        </div>
    </form>
@endsection
