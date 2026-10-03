<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SellInvestmentsForm extends Form
{
    // a string, because the browser sends the input as a string. A different type would make Livewire overwrite
    // the input field while the player is typing, whenever another player triggers a re-render (see issue #680).
    #[Validate]
    public ?string $amount = '0';

    // public properties needed for validation
    public ?InvestmentId $investmentId = null;
    public float $sharePrice = 0;
    public int $amountOwned = 0;
    // used to show others who bought the investment
    public string $playerName = '';

    /**
     * Set of custom validation rules for the form.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'amount' => [
                // bail: the closure must only run for a valid integer
                'bail', 'required', 'integer', 'min:1', function ($attribute, $value, $fail) {
                    if ((int) $value > $this->amountOwned) {
                        $fail("Du kannst nicht mehr Anteile verkaufen, als du besitzt.");
                    }
                }
            ],
        ];
    }

    /**
     * The only way to read the input: validates the form first. On invalid input, Livewire shows the
     * ValidationException as an error at the input field.
     */
    public function getValidatedAmount(): int
    {
        $this->validate();
        return (int) $this->amount;
    }
}
