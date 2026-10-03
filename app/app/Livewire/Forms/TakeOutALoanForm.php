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
    // a string, because the browser sends the input as a string. A different type would make Livewire overwrite
    // the input field while the player is typing, whenever another player triggers a re-render (see issue #680).
    #[Validate('required|integer')]
    public ?string $loanAmount = '0';

    // public properties needed for displaying the repayment in the frontend
    public float $repaymentPeriod = Configuration::REPAYMENT_PERIOD;
    public float $zinssatz = 0;

    /**
     * The only way to read the input: validates the form first. On invalid input, Livewire shows the
     * ValidationException as an error at the input field.
     */
    public function getValidatedLoanAmount(): int
    {
        $this->validate();
        return (int) $this->loanAmount;
    }
}
