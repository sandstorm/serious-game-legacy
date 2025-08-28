<?php

declare(strict_types = 1);

namespace Tests\Unit\App\Helper;

use App\Helper\KompetenzenHelper;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

describe('KompetenzenHelperTest', function () {

    it('0 out of 2', function () {
        $kompetenzen = 0;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzSteineForCategory(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component',
            CategoryId::BILDUNG_UND_KARRIERE
        );

        expect($result->kompetenzSteine)->toHaveCount(2)
            ->and($result->kompetenzSteine[0]->drawEmpty)->toBeTrue()
            ->and($result->kompetenzSteine[0]->drawHalfEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawEmpty)->toBeTrue()
            ->and($result->kompetenzSteine[1]->drawHalfEmpty)->toBeFalse()
            ->and($result->ariaLabel)->toBe('Deine Kompetenzsteine im Bereich Bildung & Karriere: 0 von 2');
    });

    it('3.5 out of 5', function () {
        $kompetenzen = 3.5;
        $requiredKompetenzen = 5;

        $result = KompetenzenHelper::getKompetenzSteineForCategory(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component',
            CategoryId::BILDUNG_UND_KARRIERE
        );

        expect($result->kompetenzSteine)->toHaveCount(5)
            ->and($result->kompetenzSteine[0]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[2]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[3]->drawHalfEmpty)->toBeTrue()
            ->and($result->kompetenzSteine[4]->drawEmpty)->toBeTrue()
            ->and($result->ariaLabel)->toBe('Deine Kompetenzsteine im Bereich Bildung & Karriere: 3.5 von 5');
    });

    it('2 out of 2', function () {
        $kompetenzen = 2;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzSteineForCategory(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component',
            CategoryId::BILDUNG_UND_KARRIERE
        );

        expect($result->kompetenzSteine)->toHaveCount(2)
            ->and($result->kompetenzSteine[0]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[0]->drawHalfEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawHalfEmpty)->toBeFalse()
            ->and($result->ariaLabel)->toBe('Deine Kompetenzsteine im Bereich Bildung & Karriere: 2 von 2');
    });

    it('3 out of 2', function () {
        $kompetenzen = 3;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzSteineForCategory(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component',
            CategoryId::BILDUNG_UND_KARRIERE
        );

        expect($result->kompetenzSteine)->toHaveCount(3)
            ->and($result->kompetenzSteine[0]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[0]->drawHalfEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawHalfEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[2]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[2]->drawHalfEmpty)->toBeFalse()
            ->and($result->ariaLabel)->toBe('Deine Kompetenzsteine im Bereich Bildung & Karriere: 3 von 2');
    });

    it('1.5 out of 2', function () {
        $kompetenzen = 1.5;
        $requiredKompetenzen = 2;

        $result = KompetenzenHelper::getKompetenzSteineForCategory(
            "color-1",
            "max",
            $kompetenzen,
            $requiredKompetenzen,
            'icon-component',
            CategoryId::BILDUNG_UND_KARRIERE
        );

        expect($result->kompetenzSteine)->toHaveCount(2)
            ->and($result->kompetenzSteine[0]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[0]->drawHalfEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawEmpty)->toBeFalse()
            ->and($result->kompetenzSteine[1]->drawHalfEmpty)->toBeTrue()
            ->and($result->ariaLabel)->toBe('Deine Kompetenzsteine im Bereich Bildung & Karriere: 1.5 von 2');
    });
});
