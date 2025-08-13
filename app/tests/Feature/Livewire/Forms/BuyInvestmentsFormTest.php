<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Forms\BuyInvestmentsForm;
use Livewire\Livewire;
use Tests\ComponentWithForm;

describe('BuyInvestmentsForm', function () {
    it('generates errors if required fields omitted', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => BuyInvestmentsForm::class,
        ])
            ->call('validate')
            ->assertHasErrors(['form.amount' => 'min:1']);
    });

    it('generates errors if player tries to buy to much investments', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => BuyInvestmentsForm::class,
        ])
            ->set('form.amount', 10)
            ->set('form.sharePrice', 100)
            ->set('form.guthaben', 500)
            ->call('validate')
            ->assertHasErrors(['form.amount' => 'Du kannst nicht mehr Anteile kaufen, als du dir leisten kannst.']);
    });

    it('does not generate errors if player can buy investments', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => BuyInvestmentsForm::class,
        ])
            ->set('form.amount', 5)
            ->set('form.sharePrice', 100)
            ->set('form.guthaben', 1000)
            ->call('validate')
            ->assertHasNoErrors();
    });
});
