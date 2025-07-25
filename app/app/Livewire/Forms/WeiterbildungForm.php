<?php
declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Livewire\Attributes\Validate;
use Livewire\Form;

class WeiterbildungForm extends Form
{
    /**
     * @var AnswerOption[]
     */
    public array $options = [];

    public string $answer;
}
