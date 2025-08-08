<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Helper\ImmobilienPriceHelper;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

describe('ImmobilienPriceHelperTest', function () {
    // run test for different purchase prices
    $purchasePrices = [new MoneyAmount(100000), new MoneyAmount(200000), new MoneyAmount(300000), new MoneyAmount(400000), new MoneyAmount(500000)];
    $konjunkturPhasen = [
        KonjunkturphaseTypeEnum::AUFSCHWUNG,
        KonjunkturphaseTypeEnum::DEPRESSION,
        KonjunkturphaseTypeEnum::REZESSION,
        KonjunkturphaseTypeEnum::BOOM
    ];


    it('calculatePriceForImmobilie', function () use ($purchasePrices, $konjunkturPhasen) {
        foreach ($konjunkturPhasen as $konjunkturphase) {
            foreach ($purchasePrices as $purchasePrice) {
                // Initial price should be equal to purchase price
                $calculatedPrice = ImmobilienPriceHelper::calculateNewPriceForImmobilie($purchasePrice, $konjunkturphase);
                echo $konjunkturphase->value . ": Purchase price " . $purchasePrice->value . " | Current Price " . $calculatedPrice->value . "\n";
            }
        }
    })->skip(" only for debugging ");
});
