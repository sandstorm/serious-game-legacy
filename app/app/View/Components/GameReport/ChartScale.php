<?php

declare(strict_types=1);

namespace App\View\Components\GameReport;

/**
 * A readable value axis for the charts of the game report.
 */
final class ChartScale
{
    /**
     * @return array{float, float, float} min, max and step of the axis, with steps of 1, 2 or 5 times a power of ten
     */
    public static function getNiceScale(float $minValue, float $maxValue): array
    {
        $range = max(1, $maxValue - $minValue);
        $roughStep = $range / 4;
        $magnitude = 10 ** floor(log10($roughStep));
        $step = $magnitude;
        foreach ([1, 2, 5, 10] as $factor) {
            if ($factor * $magnitude >= $roughStep) {
                $step = $factor * $magnitude;
                break;
            }
        }
        $min = floor($minValue / $step) * $step;
        $max = ceil($maxValue / $step) * $step;
        // a flat line still needs a visible scale
        return [$min, max($max, $min + $step), $step];
    }
}
