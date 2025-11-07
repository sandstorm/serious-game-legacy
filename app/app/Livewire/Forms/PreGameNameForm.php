<?php
declare(strict_types=1);
namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PreGameNameForm extends Form
{
    #[Validate('required', message: 'Du musst einen Namen eingeben.')]
    #[Validate('min:2', message: 'Der Name muss mindestens 2 Zeichen lang sein.')]
    #[Validate('max:12', message: 'Der Name darf maximal 12 Zeichen lang sein.')]
    public string $name = '';
}
