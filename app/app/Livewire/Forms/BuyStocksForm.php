<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class BuyStocksForm extends Form
{
    #[Validate]
    public int $amount = 0;

    public float $guthaben = 0;
    public float $price = 0;

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
                    if ($this->amount * $this->price > $this->guthaben) {
                        $fail("Du kannst nicht mehr Aktien kaufen, als du dir leisten kannst.");
                    }
                }
            ],
        ];
    }
}
