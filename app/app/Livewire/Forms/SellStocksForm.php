<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SellStocksForm extends Form
{
    #[Validate]
    public int $amount = 0;

    // public properties needed for validation
    public StockType $stockType;
    public float $sharePrice = 0;
    public int $amountOwned = 0;

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
                        $fail("Du kannst nicht mehr Aktien verkaufen, als du besitzt.");
                    }
                }
            ],
        ];
    }
}
