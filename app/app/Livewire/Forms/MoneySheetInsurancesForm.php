<?php
declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Insurance\InsuranceDefinition;
use Livewire\Form;

class MoneySheetInsurancesForm extends Form
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $insurances = [];

    public function addInsurance(int $currentPlayerPhase, InsuranceDefinition $insuranceDefinition, bool $checked = false): void
    {
        $this->insurances[$insuranceDefinition->id->value] = [
            'label' => $insuranceDefinition->getLabelWithAnnualCost($currentPlayerPhase),
            'id' => $insuranceDefinition->id->value,
            'value' => $checked,
        ];
    }
}
