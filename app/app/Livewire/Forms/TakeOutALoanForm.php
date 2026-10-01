<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Configuration\Configuration;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TakeOutALoanForm extends Form
{
    // only validates the input format - the business rules (e.g. credit limit) are validated
    // by the domain in TakeOutALoanForPlayerAktion, see HasMoneySheet::takeOutALoan()
    #[Validate('required|integer')]
    public ?int $loanAmount = 0;

    // public properties needed for displaying the repayment in the frontend
    public float $repaymentPeriod = Configuration::REPAYMENT_PERIOD;
    public float $zinssatz = 0;
}
