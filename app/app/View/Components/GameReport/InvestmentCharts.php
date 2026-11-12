<?php

declare(strict_types=1);

namespace App\View\Components\GameReport;

use Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport;
use Domain\CoreGameLogic\Feature\GameReport\Dto\InvestmentPricePoint;
use Domain\CoreGameLogic\Feature\GameReport\Dto\InvestmentTrade;
use Domain\CoreGameLogic\Feature\GameReport\Dto\PlayerReport;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * One small step chart per investment with the price over the whole game, rendered as inline SVG.
 * Uses the x-axis of the {@see GuthabenChart}: the end of the Spielzug with number t lies at x = t,
 * a year that starts after t Spielzügen at x = t + 0.5. Trades are drawn within their Spielzug at x = t + 0.75,
 * after a possible year start and before the new price at the end of the Spielzug.
 */
class InvestmentCharts extends Component
{
    private const int WIDTH = 480;
    private const int HEIGHT = 200;
    private const int MARGIN_LEFT = 64;
    private const int MARGIN_RIGHT = 12;
    private const int MARGIN_TOP = 12;
    private const int MARGIN_BOTTOM = 28;
    private const int MIN_YEAR_LABEL_WIDTH = 18;
    private const int MARKER_OFFSET = 7;

    public function __construct(
        public GameReport $report,
    ) {
    }

    public function render(): View
    {
        $charts = [];
        foreach (InvestmentId::cases() as $investmentId) {
            $chart = $this->getChart($investmentId);
            if ($chart !== null) {
                $charts[] = $chart;
            }
        }

        return view('components.game-report.investment-charts', [
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
            'plotLeft' => self::MARGIN_LEFT,
            'plotRight' => self::WIDTH - self::MARGIN_RIGHT,
            'plotTop' => self::MARGIN_TOP,
            'plotBottom' => self::HEIGHT - self::MARGIN_BOTTOM,
            'charts' => $charts,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getChart(InvestmentId $investmentId): ?array
    {
        $history = $this->report->investmentPrices;
        $points = $history->getPricesFor($investmentId);
        if ($points === []) {
            return null;
        }
        $trades = $history->getTradesFor($investmentId);
        $end = max(2, $history->getNumberOfSpielzuege() + 1);

        $values = array_map(fn (InvestmentPricePoint $point) => $point->price->value, $points);
        [$min, $max, $step] = ChartScale::getNiceScale(0, max($values));

        $x = fn (float $position): float => round(
            self::MARGIN_LEFT + $position / $end * (self::WIDTH - self::MARGIN_LEFT - self::MARGIN_RIGHT),
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

        // the price stays the same until the next price point
        $path = '';
        foreach ($points as $index => $point) {
            $pointX = $x(self::getPosition($point));
            $pointY = $y($point->price->value);
            $path .= $index === 0 ? "M{$pointX},{$pointY}" : " H{$pointX} V{$pointY}";
        }
        $path .= ' H' . $x($end);

        $first = $points[0]->price->value;
        $last = $points[count($points) - 1]->price->value;

        return [
            'name' => $investmentId->value,
            'startPrice' => number_format($first, 2, ',', '.') . ' €',
            'endPrice' => number_format($last, 2, ',', '.') . ' €',
            'change' => $first > 0 ? ($last - $first) / $first * 100 : 0.0,
            'ticks' => $ticks,
            'bands' => $this->getBands($x, $end),
            'path' => $path,
            'markers' => $this->getMarkers($trades, $x, $y),
        ];
    }

    private static function getPosition(InvestmentPricePoint $point): float
    {
        if (!$point->isYearStart) {
            return $point->turnIndex;
        }
        return $point->turnIndex === 0 ? 0 : $point->turnIndex + 0.5;
    }

    /**
     * @param \Closure(float): float $x
     * @return list<array<string, mixed>>
     */
    private function getBands(\Closure $x, int $end): array
    {
        $yearStarts = array_values($this->report->timeline->yearStarts);
        $bands = [];
        foreach ($yearStarts as $index => $yearStart) {
            $start = $index === 0 ? 0 : $yearStart->turnIndex + 0.5;
            $bandEnd = isset($yearStarts[$index + 1]) ? $yearStarts[$index + 1]->turnIndex + 0.5 : $end;
            $width = max(0, $x($bandEnd) - $x($start));
            $bands[] = [
                'x' => $x($start),
                'width' => $width,
                'labelX' => $x(($start + $bandEnd) / 2),
                'label' => $width >= self::MIN_YEAR_LABEL_WIDTH ? 'J' . $yearStart->year->value : '',
                'title' => 'Jahr ' . $yearStart->year->value . ': ' . $yearStart->name,
                'modifier' => strtolower($yearStart->type->name),
            ];
        }
        return $bands;
    }

    /**
     * Purchases are drawn below the price line pointing up, sales above it pointing down.
     * Trades in the same Spielzug are placed next to each other. Their hit area is larger than the triangle,
     * so they can be tapped on touch screens.
     *
     * @param list<InvestmentTrade> $trades
     * @param \Closure(float): float $x
     * @param \Closure(float): float $y
     * @return list<array<string, mixed>>
     */
    private function getMarkers(array $trades, \Closure $x, \Closure $y): array
    {
        $markers = [];
        /** @var array<int, int> $tradesInSpielzug */
        $tradesInSpielzug = [];
        foreach ($trades as $trade) {
            $player = $this->findPlayer($trade);
            $number = $tradesInSpielzug[$trade->turnIndex] ?? 0;
            $tradesInSpielzug[$trade->turnIndex] = $number + 1;

            $markerX = $x($trade->turnIndex + 0.75) + $number * self::MARKER_OFFSET;
            $lineY = $y($trade->price->value);
            $markerY = $trade->isSale ? $lineY - self::MARKER_OFFSET : $lineY + self::MARKER_OFFSET;
            // triangles with their tip towards the price line
            $points = $trade->isSale
                ? sprintf('%s,%s %s,%s %s,%s', $markerX - 5, $markerY - 4, $markerX + 5, $markerY - 4, $markerX, $markerY + 4)
                : sprintf('%s,%s %s,%s %s,%s', $markerX - 5, $markerY + 4, $markerX + 5, $markerY + 4, $markerX, $markerY - 4);

            $markers[] = [
                'colorClass' => $player->colorClass ?? '',
                'isSale' => $trade->isSale,
                'points' => $points,
                'x' => $markerX,
                'y' => $markerY,
                'text' => sprintf(
                    '%s: %s %s %s Anteile zu je %s',
                    $this->getYearLabel($trade->turnIndex),
                    $player->name ?? 'Unbekannt',
                    $trade->isSale ? 'verkauft' : 'kauft',
                    number_format($trade->amount, 0, ',', '.'),
                    $trade->price->formatWithoutHtml(),
                ),
            ];
        }
        return $markers;
    }

    private function getYearLabel(int $turnIndex): string
    {
        $label = '';
        foreach ($this->report->timeline->yearStarts as $yearStart) {
            if ($yearStart->turnIndex <= $turnIndex) {
                $label = 'Jahr ' . $yearStart->year->value;
            }
        }
        return $label;
    }

    private function findPlayer(InvestmentTrade $trade): ?PlayerReport
    {
        foreach ($this->report->players as $player) {
            if ($player->playerId->equals($trade->playerId)) {
                return $player;
            }
        }
        return null;
    }
}
