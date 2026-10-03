<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MoneySheetLebenshaltungskostenForm extends Form
{
    // a string, because the browser sends the input as a string. A different type would make Livewire overwrite
    // the input field while the player is typing, whenever another player triggers a re-render (see issue #680).
    #[Validate('required|numeric|decimal:0,2|min:0')]
    public ?string $lebenshaltungskosten = '0';

    // just a flag to disable the input field in the view
    public bool $isLebenshaltungskostenInputDisabled = false;

    /**
     * The only way to read the input: validates the form first. On invalid input, Livewire shows the
     * ValidationException as an error at the input field.
     */
    public function getValidatedLebenshaltungskosten(): MoneyAmount
    {
        $this->validate();
        return new MoneyAmount((float) $this->lebenshaltungskosten);
    }
}
