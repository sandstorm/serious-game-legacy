<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class TakeOutALoanForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $intendedUse = '';

    #[Validate('required|numeric|min:1')]
    public int $loanAmount = 0;

    #[Validate('required|numeric|min:1')]
    public float $repayment = 0;

    #[Validate('required|numeric|min:1')]
    public float $repaymentPerRound = 0;


}
