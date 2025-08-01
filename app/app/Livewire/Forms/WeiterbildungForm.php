<?php
declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class WeiterbildungForm extends Form
{
    #[Validate('required', message: 'Du musst eine Antwort auswählen.')]
    public string $answer;
}
