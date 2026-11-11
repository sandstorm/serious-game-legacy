<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\GameReport\State;

use Domain\CoreGameLogic\Feature\GameReport\Dto\CareerStep;
use Domain\CoreGameLogic\Feature\GameReport\Dto\Highlight;
use Domain\CoreGameLogic\Feature\GameReport\Dto\HighlightType;
use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyBreakdown;
use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyCategory;
use Domain\CoreGameLogic\Feature\GameReport\Dto\PlayerReport;
use Domain\CoreGameLogic\Feature\GameReport\State\GameReportHighlights;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Tests\DomainTestCase;

beforeEach(function () {
    /** @var DomainTestCase $this */
    $this->setupBasicGame();
});

/**
 * @param array<string, float> $amounts keyed by {@see MoneyCategory::$value}
 * @param CareerStep[] $career
 */
function playerReport(
    string $name,
    float $guthaben = 0,
    array $amounts = [],
    int $moneysheetInputs = 0,
    int $correctMoneysheetInputs = 0,
    array $career = [],
): PlayerReport {
    return new PlayerReport(
        playerId: PlayerId::fromString($name),
        name: $name,
        colorClass: 'player-color-1',
        lebenszielName: null,
        reachedPhase: LebenszielPhaseId::PHASE_1,
        phaseReachedInYear: [],
        hasFinishedLebensziel: false,
        finalGuthaben: new MoneyAmount($guthaben),
        assetsValue: new MoneyAmount(0),
        openLoanRepayments: new MoneyAmount(0),
        breakdown: new MoneyBreakdown($amounts),
        career: $career,
        investmentResults: [],
        topCardExpenses: [],
        moneysheetInputs: $moneysheetInputs,
        correctMoneysheetInputs: $correctMoneysheetInputs,
        numberOfMinijobs: 0,
    );
}

/**
 * @param PlayerReport[] $players
 */
function findHighlight(DomainTestCase $testCase, array $players, HighlightType $type): ?Highlight
{
    foreach (GameReportHighlights::find($testCase->getGameEvents(), $players, [], []) as $highlight) {
        if ($highlight->type === $type) {
            return $highlight;
        }
    }
    return null;
}

describe('GameReportHighlights::find', function () {
    it('names every player that shares the most fines', function () {
        /** @var DomainTestCase $this */
        $highlight = findHighlight($this, [
            playerReport('Anna', amounts: [MoneyCategory::STRAFEN->value => -11000]),
            playerReport('Ben', amounts: [MoneyCategory::STRAFEN->value => -11000]),
            playerReport('Cem', amounts: [MoneyCategory::STRAFEN->value => -500]),
        ], HighlightType::MOST_FINES);

        expect($highlight?->playerId)->toBeNull()
            ->and($highlight?->text)->toBe('Anna und Ben haben je 11.000,00 € Strafe für falsch ausgefüllte Moneysheets gezahlt.');
    });

    it('names the single player with the most fines', function () {
        /** @var DomainTestCase $this */
        $highlight = findHighlight($this, [
            playerReport('Anna', amounts: [MoneyCategory::STRAFEN->value => -500]),
            playerReport('Ben', amounts: [MoneyCategory::STRAFEN->value => -11000]),
        ], HighlightType::MOST_FINES);

        expect($highlight?->playerId?->value)->toBe('Ben')
            ->and($highlight?->text)->toBe('Ben hat 11.000,00 € Strafe für falsch ausgefüllte Moneysheets gezahlt.');
    });

    it('names every player that shares the most money', function () {
        /** @var DomainTestCase $this */
        $highlight = findHighlight($this, [
            playerReport('Anna', guthaben: 5000),
            playerReport('Ben', guthaben: 5000),
        ], HighlightType::MOST_MONEY);

        expect($highlight?->playerId)->toBeNull()
            ->and($highlight?->text)->toBe('Anna und Ben beenden das Spiel mit je 5.000,00 € auf dem Konto.');
    });

    it('praises the moneysheet only when at least half of the inputs were correct', function (int $correct, bool $isPraised) {
        /** @var DomainTestCase $this */
        $highlight = findHighlight($this, [
            playerReport('Anna', moneysheetInputs: 44, correctMoneysheetInputs: $correct),
            playerReport('Ben', moneysheetInputs: 40, correctMoneysheetInputs: 0),
        ], HighlightType::MOST_ACCURATE_MONEYSHEET);

        expect($highlight !== null)->toBe($isPraised);
    })->with([
        '3 of 44' => [3, false],
        '22 of 44' => [22, true],
    ]);

    it('tells the highest salary in the past tense', function () {
        /** @var DomainTestCase $this */
        $highlight = findHighlight($this, [
            playerReport('Anna', career: [new CareerStep(new Year(3), 'Fachärztin', new MoneyAmount(63000))]),
        ], HighlightType::HIGHEST_SALARY);

        expect($highlight?->text)->toBe('Anna hat als Fachärztin 63.000,00 € im Jahr verdient.');
    });
});
