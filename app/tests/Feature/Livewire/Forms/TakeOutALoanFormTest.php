<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Forms\TakeOutALoanForm;
use Livewire\Livewire;
use Tests\ComponentWithForm;

describe('TakeOutALoanForm', function () {
    it('generates errors if required fields omitted', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->call('validate')
            ->assertHasErrors(['form.loanAmount' => 'min:1']);
    });

    it('shows error when loan amount is higher than 10*guthaben (has job)', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.intendedUse', 'Investition in neue Maschinen')
            ->set('form.loanAmount', 550)
            ->set('form.guthaben', 50)
            ->set('form.zinssatz', 5)
            ->set('form.hasJob', true) // Assuming hasJob is true for this test
            ->call('validate')
            ->assertHasErrors(['form.loanAmount' => 'Du kannst keinen Kredit aufnehmen, der höher ist als das 10-fache deines aktuellen Guthabens.']);
    });

    it('shows error when loan amount is higher than 0.8*guthaben (has no job)', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.intendedUse', 'Investition in neue Maschinen')
            ->set('form.loanAmount', 81)
            ->set('form.guthaben', 100)
            ->set('form.zinssatz', 5)
            ->set('form.hasJob', false) // Assuming hasJob is false for this test
            ->call('validate')
            ->assertHasErrors(['form.loanAmount' => 'Du kannst keinen Kredit aufnehmen, der höher ist als 80% deines aktuellen Guthabens.']);
    });

    it('shows error when repayment is not correct', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.intendedUse', 'Investition in neue Maschinen')
            ->set('form.loanAmount', 10000)
            ->set('form.totalRepayment', 11000) // Incorrect repayment
            ->set('form.guthaben', 10000)
            ->set('form.hasJob', true)
            ->set('form.zinssatz', 5)
            ->call('validate')
            ->assertHasErrors(['form.totalRepayment' => 'Die Rückzahlung muss dem Kreditbetrag multipliziert mit dem Zinssatz geteilt durch 20 entsprechen.']);
    });

    it('shows error when repayment per konjunkturphase is not correct', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.intendedUse', 'Investition in neue Maschinen')
            ->set('form.loanAmount', 10000)
            ->set('form.totalRepayment', 12500)
            ->set('form.repaymentPerKonjunkturphase', 700) // Incorrect repayment per konjunkturphase
            ->set('form.guthaben', 10000)
            ->set('form.hasJob', true)
            ->set('form.zinssatz', 5)
            ->call('validate')
            ->assertHasErrors(['form.repaymentPerKonjunkturphase' => 'Die Rückzahlung pro Runde muss der Rückzahlungssumme geteilt durch 20 entsprechen.']);
    });

    it('shows no error when form is valid (has job)', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.intendedUse', 'Investition in neue Maschinen')
            ->set('form.loanAmount', 10000)
            ->set('form.totalRepayment', 12500)
            ->set('form.repaymentPerKonjunkturphase', 625)
            ->set('form.guthaben', 10000)
            ->set('form.hasJob', true)
            ->set('form.zinssatz', 5)
            ->call('validate')
            ->assertHasNoErrors();
    });

    it('shows no error when form is valid (has no job)', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => TakeOutALoanForm::class,
        ])
            ->set('form.intendedUse', 'Investition in neue Maschinen')
            ->set('form.loanAmount', 8000)
            ->set('form.totalRepayment', 10000)
            ->set('form.repaymentPerKonjunkturphase', 500)
            ->set('form.guthaben', 10000)
            ->set('form.hasJob', false)
            ->set('form.zinssatz', 5)
            ->call('validate')
            ->assertHasNoErrors();
    });
});
