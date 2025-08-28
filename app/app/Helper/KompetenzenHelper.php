<?php

declare(strict_types=1);

namespace App\Helper;

use App\Livewire\Dto\Kompetenzen;
use App\Livewire\Dto\KompetenzWithColor;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class KompetenzenHelper
{
    /**
     * @param string $colorClass
     * @param string $playerName
     * @param float $kompetenzen
     * @param int $requiredKompetenzen
     * @param string $iconComponentName
     * @return Kompetenzen
     */
    public static function getKompetenzen(
        string $colorClass,
        string $playerName,
        float $kompetenzen,
        int $requiredKompetenzen,
        string $iconComponentName,
        CategoryId $categoryId,
    ): Kompetenzen
    {
        $kompetenzenArray = [];
        for ($i = 0; $i < $kompetenzen; $i++) {
            $kompetenzenArray[] = new KompetenzWithColor(
                drawEmpty: false,
                // only possible for category bildung at the moment
                drawHalfEmpty: abs($i + 0.5 - $kompetenzen) < 0.01,
                colorClass: $colorClass,
                playerName: $playerName,
                iconComponentName: $iconComponentName,
            );
        }

        // fill up the rest with empty ones
        $slotsUsed = count($kompetenzenArray);
        for ($i = 0; $i < $requiredKompetenzen - $slotsUsed; $i++) {
            $kompetenzenArray[] = new KompetenzWithColor(
                drawEmpty: true,
                colorClass: '',
                playerName: '',
                iconComponentName: $iconComponentName,
            );
        }

        return new Kompetenzen(
            ariaLabel: 'Deine Kompetenzen im Bereich ' . $categoryId->value .  ': '. $kompetenzen . ' von ' . $requiredKompetenzen,
            kompetenzen: $kompetenzenArray
        );
    }

}
