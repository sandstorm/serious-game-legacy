<?php

declare(strict_types=1);

namespace Tests;

use Livewire\Component;
use Livewire\Features\SupportFormObjects\Form;

/**
 * Creates a Livewire component that can be used in tests with a form.
 * Its only possible to test forms when they are used in a Livewire component.
 */
class ComponentWithForm extends Component
{
    public string $formClass = '';

    public Form $form;

    public function mount($formClass): void
    {
        $this->form = new $formClass($this, 'form');
    }

    public function validate($rules = null, $messages = [], $attributes = []): void
    {
        if (method_exists($this->form, 'validate')) {
            $this->form->validate();
        }
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
