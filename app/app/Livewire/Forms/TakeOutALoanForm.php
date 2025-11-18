<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\Definitions\Configuration\Configuration;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TakeOutALoanForm extends Form
{
    #[Validate]
    public ?int $loanAmount = 0;

    public float $repaymentPeriod = Configuration::REPAYMENT_PERIOD;

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
            ]
        ];
    }
}
