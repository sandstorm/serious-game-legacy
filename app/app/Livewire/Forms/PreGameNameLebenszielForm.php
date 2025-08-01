<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PreGameNameLebenszielForm extends Form
{
    #[Validate('required|min:2|max:12')]
    public string $name = '';

    #[Validate('required', message: 'Bitte Lebensziel auswählen')]
    public ?int $lebensziel = null;
}
