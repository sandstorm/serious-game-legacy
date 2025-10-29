<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\Definitions\Configuration\Configuration;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TakeOutALoanForm extends Form
{
    public string $loanId = '';
    public string $generalError = '';

    #[Validate]
    public ?int $loanAmount = 0;

    #[Validate]
    public ?float $totalRepayment = 0;

    #[Validate]
    public ?float $repaymentPerKonjunkturphase = 0;

    // public properties needed for validation
    public float $sumOfAllAssets = 0;
    public float $obligations = 0;
    public float $zinssatz = 0;
    public float $salary = 0;
    public bool $wasPlayerInsolventInThePast = false;

    /**
     * Set of custom validation rules for the form.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $repaymentPeriod = Configuration::REPAYMENT_PERIOD;
        return [
            'loanAmount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->loanAmount > LoanCalculator::getMaxLoanAmount($this->sumOfAllAssets, $this->salary, $this->obligations, $this->wasPlayerInsolventInThePast)->value) {
                        $fail("Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.");
                    }
                }
            ],
            'totalRepayment' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($repaymentPeriod) {
                    /** @phpstan-ignore argument.type, argument.type */
                    if (!LoanCalculator::equals($this->totalRepayment, LoanCalculator::getCalculatedTotalRepayment($this->loanAmount, $this->zinssatz))) {
                        $fail("Die Rückzahlung muss dem Kreditbetrag multipliziert mit dem Zinssatz geteilt durch $repaymentPeriod entsprechen.");
                    }
                }
            ],
            'repaymentPerKonjunkturphase' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($repaymentPeriod) {
                    /** @phpstan-ignore argument.type, argument.type */
                    if (!LoanCalculator::equals($this->repaymentPerKonjunkturphase, LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($this->loanAmount, $this->zinssatz))) {
                        $fail("Die Rückzahlung pro Runde muss der Rückzahlungssumme geteilt durch $repaymentPeriod entsprechen.");
                    }
                }
            ],
        ];
    }

    /**
     * @param mixed $field
     * @return void
     */
    public function resetValidation(mixed $field = null): void
    {
        parent::resetValidation($field);
        $this->generalError = '';
    }


    public function getCalculatedRepaymentPerKonjunkturphase(): float
    {
        return LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($this->loanAmount ?? 0, $this->zinssatz);
    }
}
