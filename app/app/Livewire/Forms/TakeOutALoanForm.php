<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Configuration\Configuration;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TakeOutALoanForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $intendedUse = '';

    #[Validate]
    public int $loanAmount = 0;

    #[Validate]
    public float $totalRepayment = 0;

    #[Validate]
    public float $repaymentPerKonjunkturphase = 0;

    // public properties needed for validation
    public float $guthaben = 0;
    public float $zinssatz = 0;

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
                'required', 'numeric', 'min:1', function ($attribute, $value, $fail) {
                    if ($this->loanAmount > $this->guthaben * 10) {
                        $fail("Du kannst keinen Kredit aufnehmen, der höher ist als das 10-fache deines aktuellen Guthabens.");
                    }
                }
            ],
            'totalRepayment' => [
                'required', 'numeric', function ($attribute, $value, $fail) {
                    if ($this->totalRepayment !== $this->getCalculatedRepayment($this->zinssatz)) {
                        $fail("Die Rückzahlung muss dem Kreditbetrag multipliziert mit dem Zinssatz geteilt durch 20 entsprechen.");
                    }
                }
            ],
            'repaymentPerKonjunkturphase' => [
                'required', 'numeric', function ($attribute, $value, $fail) use ($repaymentPeriod) {
                    if ($this->repaymentPerKonjunkturphase !== $this->getCalculatedRepayment($this->zinssatz) / Configuration::REPAYMENT_PERIOD) {
                        $fail("Die Rückzahlung pro Runde muss der Rückzahlungssumme geteilt durch $repaymentPeriod entsprechen.");
                    }
                }
            ],
        ];
    }

    private function getCalculatedRepayment(float $zinssatz): float
    {
        $repaymentPeriod = Configuration::REPAYMENT_PERIOD;
        return $this->loanAmount * (1 + $zinssatz / $repaymentPeriod);
    }
}
