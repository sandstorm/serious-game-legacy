<?php

declare(strict_types=1);

namespace App\Helper;

use App\Livewire\Dto\KompetenzSteineForCategory;
use App\Livewire\Dto\KompetenzSteinWithColor;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class KompetenzenHelper
{
    /**
     * @param string $colorClass
     * @param string $playerName
     * @param float $numberOfkompetenzSteine
     * @param int $requiredNumberOfKompetenzSteine
     * @param string $iconComponentName
     * @return KompetenzSteineForCategory
     */
    public static function getKompetenzSteineForCategory(
        string     $colorClass,
        string     $playerName,
        float      $numberOfkompetenzSteine,
        int        $requiredNumberOfKompetenzSteine,
        string     $iconComponentName,
        CategoryId $categoryId,
    ): KompetenzSteineForCategory
    {
        $kompetenzSteine = [];
        for ($i = 0; $i < $numberOfkompetenzSteine; $i++) {
            $kompetenzSteine[] = new KompetenzSteinWithColor(
                drawEmpty: false,
                // only possible for category bildung at the moment
                drawHalfEmpty: abs($i + 0.5 - $numberOfkompetenzSteine) < 0.01,
                colorClass: $colorClass,
                playerName: $playerName,
                iconComponentName: $iconComponentName,
            );
        }

        // fill up the rest with empty ones
        $slotsUsed = count($kompetenzSteine);
        for ($i = 0; $i < $requiredNumberOfKompetenzSteine - $slotsUsed; $i++) {
            $kompetenzSteine[] = new KompetenzSteinWithColor(
                drawEmpty: true,
                colorClass: '',
                playerName: '',
                iconComponentName: $iconComponentName,
            );
        }

        return new KompetenzSteineForCategory(
            ariaLabel: 'Deine Kompetenzsteine im Bereich ' . $categoryId->value .  ': '. $numberOfkompetenzSteine . ' von ' . $requiredNumberOfKompetenzSteine,
            kompetenzSteine: $kompetenzSteine
        );
    }

}
