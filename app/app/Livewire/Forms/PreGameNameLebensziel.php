<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PreGameNameLebensziel extends Form
{
    #[Validate('required|min:2')]
    public string $name = '';

    #[Validate('required')]
    public string $lebensziel = '';
}
