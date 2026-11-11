<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\GameReport\Dto\CareerStep;
use Domain\CoreGameLogic\Feature\GameReport\Dto\Highlight;
use Domain\CoreGameLogic\Feature\GameReport\Dto\HighlightType;
use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyCategory;
use Domain\CoreGameLogic\Feature\GameReport\Dto\PlayerReport;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * Rule based facts for the game report. Every rule is left out when the game has no data for it.
 */
final class GameReportHighlights
{
    /**
     * The moneysheet is only praised if at least this share of the inputs was correct.
     */
    private const float MIN_RATIO_FOR_ACCURATE_MONEYSHEET = 0.5;

    /**
     * Amounts closer than a cent count as the same.
     */
    private const float TIE_TOLERANCE = 0.005;

    /**
     * @param PlayerReport[] $players
     * @param array<array{playerId: PlayerId, title: string, saved: float}> $insuranceSavings
     * @param InvestmentPrice[] $initialPrices
     * @return Highlight[]
     */
    public static function find(GameEvents $gameEvents, array $players, array $insuranceSavings, array $initialPrices): array
    {
        $highlights = [
            self::mostMoney($players),
            self::bestInvestment($players),
            self::worstInvestment($players),
            self::biggestCardExpense($players),
            self::mostAccurateMoneysheet($players),
            self::mostFines($players),
            ...self::insurancesThatPaidOff($players, $insuranceSavings),
            self::mostJobs($players),
            self::highestSalary($players),
            self::missedChance($gameEvents, $players, $initialPrices),
        ];
        return array_values(array_filter($highlights));
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function mostMoney(array $players): ?Highlight
    {
        $leaders = self::getLeaders($players, fn (PlayerReport $player) => $player->finalGuthaben->value);
        if (count($leaders) === 0) {
            return null;
        }
        $amount = $leaders[0]->finalGuthaben->formatWithoutHtml();
        return new Highlight(
            type: HighlightType::MOST_MONEY,
            playerId: self::getPlayerIdIfAlone($leaders),
            title: 'Am meisten Guthaben',
            text: count($leaders) === 1
                ? sprintf('%s beendet das Spiel mit %s auf dem Konto.', $leaders[0]->name, $amount)
                : sprintf('%s beenden das Spiel mit je %s auf dem Konto.', self::joinNames($leaders), $amount),
        );
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function bestInvestment(array $players): ?Highlight
    {
        $best = null;
        foreach ($players as $player) {
            foreach ($player->investmentResults as $result) {
                if ($result->realizedGain->value > 0 && ($best === null || $result->realizedGain->value > $best[1]->realizedGain->value)) {
                    $best = [$player, $result];
                }
            }
        }
        if ($best === null) {
            return null;
        }
        [$player, $result] = $best;
        return new Highlight(
            type: HighlightType::BEST_INVESTMENT,
            playerId: $player->playerId,
            title: 'Bestes Investment',
            text: sprintf('%s hat mit %s %s Gewinn gemacht.', $player->name, $result->investmentId->value, $result->realizedGain->formatWithoutHtml()),
        );
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function worstInvestment(array $players): ?Highlight
    {
        $worst = null;
        foreach ($players as $player) {
            foreach ($player->investmentResults as $result) {
                if ($result->realizedGain->value < 0 && ($worst === null || $result->realizedGain->value < $worst[1]->realizedGain->value)) {
                    $worst = [$player, $result];
                }
            }
        }
        if ($worst === null) {
            return null;
        }
        [$player, $result] = $worst;
        return new Highlight(
            type: HighlightType::WORST_INVESTMENT,
            playerId: $player->playerId,
            title: 'Teuerster Fehlgriff',
            text: sprintf('%s hat mit %s %s verloren.', $player->name, $result->investmentId->value, $result->realizedGain->negate()->formatWithoutHtml()),
        );
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function biggestCardExpense(array $players): ?Highlight
    {
        $best = null;
        foreach ($players as $player) {
            $expense = $player->topCardExpenses[0] ?? null;
            if ($expense !== null && ($best === null || $expense->amount->value > $best[1]->amount->value)) {
                $best = [$player, $expense];
            }
        }
        if ($best === null) {
            return null;
        }
        [$player, $expense] = $best;
        return new Highlight(
            type: HighlightType::BIGGEST_CARD_EXPENSE,
            playerId: $player->playerId,
            title: 'Teuerste Karte',
            text: sprintf('%s hat in Jahr %d %s für „%s“ ausgegeben.', $player->name, $expense->year->value, $expense->amount->formatWithoutHtml(), $expense->title),
        );
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function mostAccurateMoneysheet(array $players): ?Highlight
    {
        $best = null;
        $bestRatio = self::MIN_RATIO_FOR_ACCURATE_MONEYSHEET;
        foreach ($players as $player) {
            if ($player->moneysheetInputs === 0) {
                continue;
            }
            $ratio = $player->correctMoneysheetInputs / $player->moneysheetInputs;
            if ($ratio > $bestRatio
                || ($ratio === $bestRatio && ($best === null || $player->moneysheetInputs > $best->moneysheetInputs))
            ) {
                $best = $player;
                $bestRatio = $ratio;
            }
        }
        if ($best === null) {
            return null;
        }
        return new Highlight(
            type: HighlightType::MOST_ACCURATE_MONEYSHEET,
            playerId: $best->playerId,
            title: 'Genaueste Buchhaltung',
            text: sprintf('%s hat %d von %d Eingaben im Moneysheet direkt richtig ausgefüllt.', $best->name, $best->correctMoneysheetInputs, $best->moneysheetInputs),
        );
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function mostFines(array $players): ?Highlight
    {
        $leaders = self::getLeaders(
            array_filter($players, fn (PlayerReport $player) => $player->breakdown->get(MoneyCategory::STRAFEN)->value < 0),
            fn (PlayerReport $player) => -$player->breakdown->get(MoneyCategory::STRAFEN)->value
        );
        if (count($leaders) === 0) {
            return null;
        }
        $amount = $leaders[0]->breakdown->get(MoneyCategory::STRAFEN)->negate()->formatWithoutHtml();
        return new Highlight(
            type: HighlightType::MOST_FINES,
            playerId: self::getPlayerIdIfAlone($leaders),
            title: 'Die meisten Strafen',
            text: count($leaders) === 1
                ? sprintf('%s hat %s Strafe für falsch ausgefüllte Moneysheets gezahlt.', $leaders[0]->name, $amount)
                : sprintf('%s haben je %s Strafe für falsch ausgefüllte Moneysheets gezahlt.', self::joinNames($leaders), $amount),
        );
    }

    /**
     * @param PlayerReport[] $players
     * @param array<array{playerId: PlayerId, title: string, saved: float}> $insuranceSavings
     * @return Highlight[]
     */
    private static function insurancesThatPaidOff(array $players, array $insuranceSavings): array
    {
        $highlights = [];
        foreach ($insuranceSavings as $saving) {
            $player = self::findPlayer($players, $saving['playerId']);
            $highlights[] = new Highlight(
                type: HighlightType::INSURANCE_PAID_OFF,
                playerId: $saving['playerId'],
                title: 'Versicherung hat sich gelohnt',
                text: sprintf('Beim Ereignis „%s“ hat die Versicherung %s %s erspart.', $saving['title'], $player->name, new MoneyAmount($saving['saved'])->formatWithoutHtml()),
            );
        }
        return $highlights;
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function mostJobs(array $players): ?Highlight
    {
        $leaders = self::getLeaders(
            array_filter($players, fn (PlayerReport $player) => $player->getNumberOfJobs() > 0),
            fn (PlayerReport $player) => $player->getNumberOfJobs()
        );
        if (count($leaders) === 0) {
            return null;
        }
        $numberOfJobs = $leaders[0]->getNumberOfJobs();
        $jobs = $numberOfJobs === 1 ? 'Job' : 'Jobs';
        return new Highlight(
            type: HighlightType::MOST_JOBS,
            playerId: self::getPlayerIdIfAlone($leaders),
            title: 'Die meisten Jobs',
            text: count($leaders) === 1
                ? sprintf('%s hat %d %s angenommen.', $leaders[0]->name, $numberOfJobs, $jobs)
                : sprintf('%s haben je %d %s angenommen.', self::joinNames($leaders), $numberOfJobs, $jobs),
        );
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function highestSalary(array $players): ?Highlight
    {
        $best = null;
        foreach ($players as $player) {
            foreach ($player->career as $step) {
                if ($step->gehalt !== null && ($best === null || $step->gehalt->value > ($best[1]->gehalt->value ?? 0))) {
                    $best = [$player, $step];
                }
            }
        }
        if ($best === null) {
            return null;
        }
        /** @var array{PlayerReport, CareerStep} $best */
        [$player, $step] = $best;
        return new Highlight(
            type: HighlightType::HIGHEST_SALARY,
            playerId: $player->playerId,
            title: 'Höchstes Gehalt',
            text: sprintf('%s hat als %s %s im Jahr verdient.', $player->name, $step->title, $step->gehalt?->formatWithoutHtml()),
        );
    }

    /**
     * The investment with the biggest price increase that nobody bought.
     *
     * @param PlayerReport[] $players
     * @param InvestmentPrice[] $initialPrices
     */
    private static function missedChance(GameEvents $gameEvents, array $players, array $initialPrices): ?Highlight
    {
        $bought = [];
        foreach ($players as $player) {
            foreach ($player->investmentResults as $result) {
                $bought[$result->investmentId->value] = true;
            }
        }

        $best = null;
        $bestRatio = 1.0;
        foreach ($initialPrices as $initialPrice) {
            if (isset($bought[$initialPrice->investmentId->value]) || $initialPrice->price->value <= 0) {
                continue;
            }
            $finalPrice = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $initialPrice->investmentId);
            $ratio = $finalPrice->value / $initialPrice->price->value;
            if ($ratio > $bestRatio) {
                $best = [$initialPrice, $finalPrice];
                $bestRatio = $ratio;
            }
        }
        if ($best === null) {
            return null;
        }
        [$initialPrice, $finalPrice] = $best;
        return new Highlight(
            type: HighlightType::MISSED_CHANCE,
            playerId: null,
            title: 'Verpasste Chance',
            text: sprintf(
                'Niemand hat %s gekauft. Der Kurs stieg von %s auf %s (+%s %%).',
                $initialPrice->investmentId->value,
                $initialPrice->price->formatWithoutHtml(),
                $finalPrice->formatWithoutHtml(),
                number_format(($bestRatio - 1) * 100, 0, ',', '.')
            ),
        );
    }

    /**
     * All players with the highest score, so ties are not decided by the player order.
     *
     * @param PlayerReport[] $players
     * @param callable(PlayerReport): (int|float) $score
     * @return list<PlayerReport>
     */
    private static function getLeaders(array $players, callable $score): array
    {
        $leaders = [];
        $highestScore = null;
        foreach ($players as $player) {
            $playerScore = $score($player);
            if ($highestScore === null || $playerScore > $highestScore + self::TIE_TOLERANCE) {
                $leaders = [$player];
                $highestScore = $playerScore;
            } elseif (abs($playerScore - $highestScore) <= self::TIE_TOLERANCE) {
                $leaders[] = $player;
            }
        }
        return $leaders;
    }

    /**
     * @param list<PlayerReport> $players
     */
    private static function getPlayerIdIfAlone(array $players): ?PlayerId
    {
        return count($players) === 1 ? $players[0]->playerId : null;
    }

    /**
     * "Anna", "Anna und Ben", "Anna, Ben und Cem"
     *
     * @param list<PlayerReport> $players
     */
    private static function joinNames(array $players): string
    {
        $names = array_map(fn (PlayerReport $player) => $player->name, $players);
        $last = array_pop($names);
        return count($names) === 0 ? (string) $last : implode(', ', $names) . ' und ' . $last;
    }

    /**
     * @param PlayerReport[] $players
     */
    private static function findPlayer(array $players, PlayerId $playerId): PlayerReport
    {
        foreach ($players as $player) {
            if ($player->playerId->equals($playerId)) {
                return $player;
            }
        }
        throw new \InvalidArgumentException('Player ' . $playerId . ' is not part of the game report', 1794414937);
    }
}
