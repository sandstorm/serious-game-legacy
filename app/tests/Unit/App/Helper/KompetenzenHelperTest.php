<?php

declare(strict_types = 1);

namespace Tests\Unit\App\Helper;

use App\Helper\KompetenzenHelper;

describe('KompetenzenHelperTest', function () {

    it('0 out of 2', function () {
        $kompetenzen = 0;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzen(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component'
        );

        expect($result)->toHaveCount(2)
            ->and($result[0]->drawEmpty)->toBeTrue()
            ->and($result[0]->drawHalfEmpty)->toBeFalse()
            ->and($result[1]->drawEmpty)->toBeTrue()
            ->and($result[1]->drawHalfEmpty)->toBeFalse();
    });

    it('3.5 out of 5', function () {
        $kompetenzen = 3.5;
        $requiredKompetenzen = 5;

        $result = KompetenzenHelper::getKompetenzen(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component'
        );

        expect($result)->toHaveCount(5)
            ->and($result[0]->drawEmpty)->toBeFalse()
            ->and($result[1]->drawEmpty)->toBeFalse()
            ->and($result[2]->drawEmpty)->toBeFalse()
            ->and($result[3]->drawHalfEmpty)->toBeTrue()
            ->and($result[4]->drawEmpty)->toBeTrue();
    });

    it('2 out of 2', function () {
        $kompetenzen = 2;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzen(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component'
        );

        expect($result)->toHaveCount(2)
            ->and($result[0]->drawEmpty)->toBeFalse()
            ->and($result[0]->drawHalfEmpty)->toBeFalse()
            ->and($result[1]->drawEmpty)->toBeFalse()
            ->and($result[1]->drawHalfEmpty)->toBeFalse();
    });

    it('3 out of 2', function () {
        $kompetenzen = 3;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzen(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component'
        );

        expect($result)->toHaveCount(3)
            ->and($result[0]->drawEmpty)->toBeFalse()
            ->and($result[0]->drawHalfEmpty)->toBeFalse()
            ->and($result[1]->drawEmpty)->toBeFalse()
            ->and($result[1]->drawHalfEmpty)->toBeFalse()
            ->and($result[2]->drawEmpty)->toBeFalse()
            ->and($result[2]->drawHalfEmpty)->toBeFalse();
    });

    it('1.5 out of 2', function () {
        $kompetenzen = 1.5;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzen(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component'
        );

        expect($result)->toHaveCount(2)
            ->and($result[0]->drawEmpty)->toBeFalse()
            ->and($result[0]->drawHalfEmpty)->toBeFalse()
            ->and($result[1]->drawEmpty)->toBeFalse()
            ->and($result[1]->drawHalfEmpty)->toBeTrue();
    });
});
