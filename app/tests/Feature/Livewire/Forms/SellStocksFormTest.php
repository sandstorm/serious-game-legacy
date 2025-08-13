<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Forms\SellInvestmentsForm;
use Livewire\Livewire;
use Tests\ComponentWithForm;

describe('SellStocksForm', function () {
    it('generates errors if required fields omitted', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => SellInvestmentsForm::class,
        ])
            ->call('validate')
            ->assertHasErrors(['form.amount' => 'min:1']);
    });

    it('generates errors if player tries to sell to more stocks than they own', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => SellInvestmentsForm::class,
        ])
            ->set('form.amount', 10)
            ->set('form.sharePrice', 100)
            ->set('form.amountOwned', 5)
            ->call('validate')
            ->assertHasErrors(['form.amount' => 'Du kannst nicht mehr Aktien verkaufen, als du besitzt.']);
    });

    it('does not generate errors if player can sell stocks', function () {
        Livewire::test(ComponentWithForm::class, [
            'formClass' => SellInvestmentsForm::class,
        ])
            ->set('form.amount', 10)
            ->set('form.sharePrice', 100)
            ->set('form.amountOwned', 10)
            ->call('validate')
            ->assertHasNoErrors();
    });
});
