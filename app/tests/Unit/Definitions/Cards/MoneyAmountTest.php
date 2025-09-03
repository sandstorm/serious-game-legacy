<?php

declare(strict_types = 1);

namespace Tests\Unit\Definitions\Cards;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

describe('MoneyAmountTest', function () {

    it('can be created from a float', function () {
        $amount = new MoneyAmount(100.50);
        expect($amount->value)->toEqual(100.50);
    });

    it('can be created from a string', function () {
        $amount = MoneyAmount::fromString('200.75');
        expect($amount->value)->toEqual(200.75);
    });

    it('can be added to another MoneyAmount', function ($amount1, $amount2, $expected) {
        $result = new MoneyAmount($amount1)->add(new MoneyAmount($amount2));
        expect($result->value)->toEqual($expected);
    })->with([
        [100.00, 50.00, 150.00],
        [200.00, 75.50, 275.50],
        [0.99, 0.01, 1.00],
        [-100.00, -50.00, -150.00],
        [-50, 100, 50]
    ]);

    it('can be subtracted from another MoneyAmount', function ($amount1, $amount2, $expected) {
        $result = new MoneyAmount($amount1)->subtract(new MoneyAmount($amount2));
        expect($result->value)->toEqual($expected);
    })->with([
        [100.00, 50.00, 50.00],
        [200.00, 75.50, 124.50],
        [0.99, 0.01, 0.98],
        [-100.00, -50.00, -50.00],
        [-100.00, 50.00, -150.00],
        [50, -100, 150]
    ]);

    it('can be multiplied by another MoneyAmount', function () {
        $amount1 = new MoneyAmount(10.00);
        $amount2 = new MoneyAmount(5.00);
        $result = $amount1->multiply($amount2);
        expect($result->value)->toEqual(50.00);
    });

    it('can check equality with another MoneyAmount', function () {
        $amount1 = new MoneyAmount(100.00);
        $amount2 = new MoneyAmount(100.0001); // within tolerance
        expect($amount1->equals($amount2))->toBeTrue();
    });

    it('can format the value as a string', function () {
        $amount = new MoneyAmount(1234.564);
        expect($amount->format())->toEqual("<span class='text--currency'>1.234,56 €</span>");
    });

    it('can format with icon', function($amount, $expected) {
        $moneyAmount = new MoneyAmount($amount);
        expect($moneyAmount->formatWithIcon())->toEqual($expected);
    })->with([
        [10000, "<span class='text--currency'><i aria-hidden='true' class='text--success icon-plus'></i><span class='sr-only'>+</span> 10.000,00 <i aria-hidden='true' class='icon-euro'></i><span class='sr-only'>€</span></span>"],
        [-10000, "<span class='text--currency'><i aria-hidden='true' class='text--danger icon-minus'></i><span class='sr-only'>-</span> 10.000,00 <i aria-hidden='true' class='icon-euro'></i><span class='sr-only'>€</span></span>"],
        [0, "<span class='text--currency'> 0,00 <i aria-hidden='true' class='icon-euro'></i><span class='sr-only'>€</span></span>"],
    ]);
});
