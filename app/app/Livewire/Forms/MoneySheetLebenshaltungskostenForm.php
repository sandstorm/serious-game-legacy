<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class MoneySheetLebenshaltungskostenForm extends Form
{
    #[Validate('required|numeric|min:5000')]
    public float $lebenshaltungskosten = 0;

    // just a flag to disable the input field in the view
    public bool $lebenshaltungskostenIsDisabled = false;
}
