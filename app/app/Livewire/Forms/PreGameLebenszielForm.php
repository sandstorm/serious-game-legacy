<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PreGameLebenszielForm extends Form
{
    #[Validate('required', message: 'Bitte Lebensziel auswählen')]
    public ?int $lebensziel = null;
}
