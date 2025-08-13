<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SellInvestmentsForm extends Form
{
    #[Validate]
    public int $amount = 0;

    // public properties needed for validation
    public ?InvestmentId $investmentId;
    public float $sharePrice = 0;
    public int $amountOwned = 0;
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
                'required', 'numeric', 'min:1', function ($attribute, $value, $fail) {
                    if ($this->amount > $this->amountOwned) {
                        $fail("Du kannst nicht mehr Anteile verkaufen, als du besitzt.");
                    }
                }
            ],
        ];
    }
}
