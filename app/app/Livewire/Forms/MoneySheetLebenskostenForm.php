<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class MoneySheetLebenskostenForm extends Form
{
    #[Validate('required|numeric|min:5000')]
    public float $lebenskosten = 0;

    // just a flag to disable the input field in the view
    public bool $lebenskostenIsDisabled = false;
}
