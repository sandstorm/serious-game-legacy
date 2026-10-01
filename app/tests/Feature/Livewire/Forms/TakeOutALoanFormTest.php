<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Forms\TakeOutALoanForm;
use Livewire\Livewire;
use Tests\ComponentWithForm;

// The form only validates the input format. The business rules (e.g. credit limit) are validated by the domain,
// see TakeOutLoanTest, IsLoanAmountWithinLimitValidatorTest and GameUiTest.
describe('TakeOutALoanForm', function () {
    it('generates errors if required fields omitted', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.loanAmount', null)
            ->call('validate')
            ->assertHasErrors(['form.loanAmount' => 'required']);
    });

    it('shows no error when form is valid', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.loanAmount', 8000)
            ->set('form.zinssatz', 5)
            ->call('validate')
            ->assertHasNoErrors();
    });
});
