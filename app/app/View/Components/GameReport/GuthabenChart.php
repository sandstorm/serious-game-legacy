<?php

declare(strict_types=1);

namespace App\View\Components\GameReport;

use Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Line chart of the Guthaben of every player after each Spielzug, rendered as inline SVG.
 * The value at index i is drawn at x = i + 1, so the start of a year after t Spielzügen lies at x = t + 0.5.
 */
class GuthabenChart extends Component
{
    private const int WIDTH = 960;
    private const int HEIGHT = 320;
    private const int MARGIN_LEFT = 84;
    private const int MARGIN_RIGHT = 96;
    private const int MARGIN_TOP = 16;
    private const int MARGIN_BOTTOM = 32;
    private const int MIN_LABEL_DISTANCE = 16;

    public function __construct(
        public GameReport $report,
    ) {
    }

    public function render(): View
    {
        $timeline = $this->report->timeline;
        $numberOfValues = max(2, $timeline->getNumberOfValues());
        [$min, $max, $step] = ChartScale::getNiceScale($timeline->getMinValue(), $timeline->getMaxValue());

        $x = fn (float $position): float => round(
            self::MARGIN_LEFT + $position / $numberOfValues * (self::WIDTH - self::MARGIN_LEFT - self::MARGIN_RIGHT),
            1
        );
        $y = fn (float $value): float => round(
            self::MARGIN_TOP + (1 - ($value - $min) / ($max - $min)) * (self::HEIGHT - self::MARGIN_TOP - self::MARGIN_BOTTOM),
            1
        );

        $ticks = [];
        for ($value = $min; $value <= $max + 0.001; $value += $step) {
            $ticks[] = ['y' => $y($value), 'label' => number_format($value, 0, ',', '.') . ' €'];
        }

        $yearStarts = array_values($timeline->yearStarts);
        $bands = [];
        foreach ($yearStarts as $index => $yearStart) {
            $start = $index === 0 ? 0 : $yearStart->turnIndex + 0.5;
            $end = isset($yearStarts[$index + 1]) ? $yearStarts[$index + 1]->turnIndex + 0.5 : $numberOfValues;
            $bands[] = [
                'x' => $x($start),
                'width' => max(0, $x($end) - $x($start)),
                'labelX' => $x(($start + $end) / 2),
                'label' => 'J' . $yearStart->year->value,
                'title' => 'Jahr ' . $yearStart->year->value . ': ' . $yearStart->name,
                'modifier' => strtolower($yearStart->type->name),
            ];
        }

        $lines = [];
        foreach ($this->report->players as $player) {
            $values = $timeline->getValuesForPlayer($player->playerId);
            $points = [];
            foreach ($values as $index => $value) {
                $points[] = $x($index + 1) . ',' . $y($value);
            }
            $last = end($values);
            $lines[] = [
                'name' => $player->name,
                'colorClass' => $player->colorClass,
                'points' => implode(' ', $points),
                'endX' => $x(count($values)),
                'endY' => $y($last === false ? 0 : $last),
                'labelY' => $y($last === false ? 0 : $last),
            ];
        }
        // keep the direct labels at the end of the lines readable
        usort($lines, fn (array $a, array $b) => $a['labelY'] <=> $b['labelY']);
        for ($i = 1; $i < count($lines); $i++) {
            if ($lines[$i]['labelY'] - $lines[$i - 1]['labelY'] < self::MIN_LABEL_DISTANCE) {
                $lines[$i]['labelY'] = $lines[$i - 1]['labelY'] + self::MIN_LABEL_DISTANCE;
            }
        }

        return view('components.game-report.guthaben-chart', [
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
            'plotLeft' => self::MARGIN_LEFT,
            'plotRight' => self::WIDTH - self::MARGIN_RIGHT,
            'plotTop' => self::MARGIN_TOP,
            'plotBottom' => self::HEIGHT - self::MARGIN_BOTTOM,
            'ticks' => $ticks,
            'bands' => $bands,
            'lines' => $lines,
            'hoverData' => $this->getHoverData($numberOfValues),
        ]);
    }

    /**
     * Data for the tooltip, one entry per value index.
     *
     * @return array{count: int, years: list<string>, players: list<array{name: string, colorClass: string, values: list<float>}>}
     */
    private function getHoverData(int $numberOfValues): array
    {
        $timeline = $this->report->timeline;
        $years = [];
        for ($index = 0; $index < $timeline->getNumberOfValues(); $index++) {
            $label = '';
            foreach ($timeline->yearStarts as $yearStart) {
                if ($yearStart->turnIndex <= $index) {
                    $label = 'Jahr ' . $yearStart->year->value . ' · ' . $yearStart->type->value;
                }
            }
            $years[] = $label;
        }

        return [
            'count' => $numberOfValues,
            'years' => $years,
            'players' => array_values(array_map(fn ($player) => [
                'name' => $player->name,
                'colorClass' => $player->colorClass,
                'values' => $timeline->getValuesForPlayer($player->playerId),
            ], $this->report->players)),
        ];
    }
}
