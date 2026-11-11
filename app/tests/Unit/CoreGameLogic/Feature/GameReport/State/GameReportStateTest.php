<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\GameReport\State;

use Domain\CoreGameLogic\Feature\GameReport\Dto\HighlightType;
use Domain\CoreGameLogic\Feature\GameReport\Dto\KeyMomentType;
use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyCategory;
use Domain\CoreGameLogic\Feature\GameReport\State\GameReportState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\DomainTestCase;

beforeEach(function () {
    /** @var DomainTestCase $this */
    $this->setupBasicGame();
});

/**
 * Starts a new year in which every player has a single Zeitstein, so one round ends the year.
 *
 * @param CardDefinition[] $cardsOnTop
 */
function startYearWithOneZeitsteinPerPlayer(DomainTestCase $testCase, array $cardsOnTop = []): void
{
    $definition = $testCase->getKonjunkturphaseDefinition();
    assert($definition !== null);
    $oneZeitsteinPerPlayer = new KonjunkturphaseDefinition(
        id: $definition->id,
        type: $definition->type,
        name: $definition->name,
        description: $definition->description,
        additionalEvents: $definition->additionalEvents,
        zeitsteine: new Zeitsteine([new ZeitsteinePerPlayer(2, 1)]),
        kompetenzbereiche: $definition->kompetenzbereiche,
        modifierIds: $definition->modifierIds,
        modifierParameters: $definition->modifierParameters,
        auswirkungen: $definition->auswirkungen,
    );
    KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([$oneZeitsteinPerPlayer]);
    $testCase->setKonjunkturphaseDefinition($oneZeitsteinPerPlayer);
    $testCase->startNewKonjunkturphaseWithCardsOnTop($cardsOnTop);
}

function doMinijobInSpielzug(DomainTestCase $testCase, PlayerId $playerId): void
{
    $testCase->handle(new StartSpielzug($playerId));
    $testCase->handle(DoMinijob::create($playerId));
    $testCase->handle(new EndSpielzug($playerId));
}

function completeMoneysheetsOfAllPlayers(DomainTestCase $testCase): void
{
    $gameEvents = $testCase->getGameEvents();
    foreach ($testCase->getPlayers() as $playerId) {
        $testCase->handle(EnterLebenshaltungskostenForPlayer::create(
            $playerId,
            MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $playerId)
        ));
        $testCase->handle(EnterSteuernUndAbgabenForPlayer::create(
            $playerId,
            MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $playerId)
        ));
        $testCase->handle(CompleteMoneysheetForPlayer::create($playerId));
        $testCase->handle(MarkPlayerAsReadyForKonjunkturphaseChange::create($playerId));
    }
}

function playOneYearWithMinijobs(DomainTestCase $testCase): void
{
    startYearWithOneZeitsteinPerPlayer($testCase);
    foreach ($testCase->getPlayers() as $playerId) {
        doMinijobInSpielzug($testCase, $playerId);
    }
    completeMoneysheetsOfAllPlayers($testCase);
}

describe('GameReportState::getGameReport', function () {
    it('balances the money breakdown of every player with the final Guthaben', function () {
        /** @var DomainTestCase $this */
        [$first, $second] = $this->getPlayers();
        startYearWithOneZeitsteinPerPlayer($this);
        $this->handle(new StartSpielzug($first));
        $this->handle(ActivateCard::create($first, CategoryId::BILDUNG_UND_KARRIERE));
        $this->handle(new EndSpielzug($first));
        doMinijobInSpielzug($this, $second);
        completeMoneysheetsOfAllPlayers($this);
        playOneYearWithMinijobs($this);

        $gameEvents = $this->getGameEvents();
        $report = GameReportState::getGameReport($gameEvents);

        foreach ($this->getPlayers() as $playerId) {
            $playerReport = $report->getPlayer($playerId);
            $guthaben = PlayerState::getGuthabenForPlayer($gameEvents, $playerId);
            expect($playerReport->finalGuthaben->value)->toEqualWithDelta($guthaben->value, 0.001)
                ->and($playerReport->breakdown->getTotal()->value)->toEqualWithDelta($guthaben->value, 0.001)
                ->and($playerReport->breakdown->get(MoneyCategory::STARTKAPITAL)->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
                ->and($playerReport->breakdown->get(MoneyCategory::SONSTIGES)->value)->toEqual(0);
        }
    });

    it('splits the yearly moneysheet booking into Gehalt, Steuern and Lebenshaltungskosten', function () {
        /** @var DomainTestCase $this */
        startYearWithOneZeitsteinPerPlayer($this, [
            new JobCardDefinition(
                id: new CardId('job1'),
                title: 'Fachkraft für Tests',
                description: '...',
                gehalt: new MoneyAmount(40000),
                requirements: new JobRequirements(zeitsteine: 1),
            ),
        ]);
        $player = $this->getPlayers()[0];
        $this->handle(new StartSpielzug($player));
        $this->handle(AcceptJobOffer::create($player, new CardId('job1')));
        $this->handle(new EndSpielzug($player));
        doMinijobInSpielzug($this, $this->getPlayers()[1]);
        $eventsBeforeMoneysheet = $this->getGameEvents();
        $expectedSteuern = MoneySheetState::calculateSteuernUndAbgabenForPlayer($eventsBeforeMoneysheet, $player);
        $expectedLebenshaltung = MoneySheetState::calculateLebenshaltungskostenForPlayer($eventsBeforeMoneysheet, $player);
        completeMoneysheetsOfAllPlayers($this);

        $breakdown = GameReportState::getGameReport($this->getGameEvents())->getPlayer($player)->breakdown;

        expect($breakdown->get(MoneyCategory::GEHALT)->value)->toEqual(40000)
            ->and($breakdown->get(MoneyCategory::STEUERN)->value)->toEqual(-$expectedSteuern->value)
            ->and($breakdown->get(MoneyCategory::LEBENSHALTUNG)->value)->toEqual(-$expectedLebenshaltung->value)
            ->and($breakdown->get(MoneyCategory::SONSTIGES)->value)->toEqual(0);
    });

    it('records the career of a player', function () {
        /** @var DomainTestCase $this */
        $this->startNewKonjunkturphaseWithCardsOnTop([
            new JobCardDefinition(
                id: new CardId('job1'),
                title: 'Fachkraft für Tests',
                description: '...',
                gehalt: new MoneyAmount(40000),
                requirements: new JobRequirements(zeitsteine: 1),
            ),
        ]);
        $player = $this->getPlayers()[0];
        $this->handle(new StartSpielzug($player));
        $this->handle(AcceptJobOffer::create($player, new CardId('job1')));

        $report = GameReportState::getGameReport($this->getGameEvents());
        $career = $report->getPlayer($player)->career;

        expect($career)->toHaveCount(1)
            ->and($career[0]->title)->toBe('Fachkraft für Tests')
            ->and($career[0]->gehalt?->value)->toEqual(40000)
            ->and($career[0]->year->value)->toBe(KonjunkturphaseState::getCurrentYear($this->getGameEvents())->value)
            ->and($report->getPlayer($this->getPlayers()[1])->career)->toBeEmpty();
    });

    it('books loans as income and shows them as a key moment', function () {
        /** @var DomainTestCase $this */
        $player = $this->getPlayers()[0];
        $this->handle(new StartSpielzug($player));
        $this->handle(TakeOutALoanForPlayer::create($player, 10000));

        $report = GameReportState::getGameReport($this->getGameEvents());

        expect($report->getPlayer($player)->breakdown->get(MoneyCategory::KREDITE)->value)->toEqual(10000)
            ->and(array_map(fn ($moment) => $moment->type, $report->keyMoments))->toContain(KeyMomentType::LOAN_TAKEN);
    });

    it('calculates the realized gain of sold investments', function () {
        /** @var DomainTestCase $this */
        [$first, $second] = $this->getPlayers();
        $this->handle(new StartSpielzug($first));
        $this->handle(BuyInvestmentsForPlayer::create($first, InvestmentId::MERFEDES_PENZ, 100));
        $this->handle(DontSellInvestmentsForPlayer::create($second, InvestmentId::MERFEDES_PENZ));
        $this->handle(new EndSpielzug($first));
        $this->handle(new StartSpielzug($second));
        $this->handle(DoMinijob::create($second));
        $this->handle(new EndSpielzug($second));
        $this->handle(new StartSpielzug($first));
        $this->handle(SellInvestmentsForPlayer::create($first, InvestmentId::MERFEDES_PENZ, 100));

        $gameEvents = $this->getGameEvents();
        /** @var PlayerHasBoughtInvestment $buy */
        $buy = $gameEvents->findLast(PlayerHasBoughtInvestment::class);
        /** @var PlayerHasSoldInvestment $sale */
        $sale = $gameEvents->findLast(PlayerHasSoldInvestment::class);
        $expectedGain = ($sale->price->value - $buy->price->value) * 100;

        $report = GameReportState::getGameReport($gameEvents);
        $results = $report->getPlayer($first)->investmentResults;

        expect($results)->toHaveCount(1)
            ->and($results[0]->investmentId)->toBe(InvestmentId::MERFEDES_PENZ)
            ->and($results[0]->realizedGain->value)->toEqualWithDelta($expectedGain, 0.01)
            ->and($results[0]->amountHeld)->toBe(0);

        $expectedHighlight = $expectedGain >= 0 ? HighlightType::BEST_INVESTMENT : HighlightType::WORST_INVESTMENT;
        expect(array_map(fn ($highlight) => $highlight->type, $report->highlights))->toContain($expectedHighlight);
    });

    it('names the winner and shows the Lebensziel progress', function () {
        /** @var DomainTestCase $this */
        $this->letFirstPlayerFinishLebensziel();
        [$first, $second] = $this->getPlayers();

        $gameEvents = $this->getGameEvents();
        $report = GameReportState::getGameReport($gameEvents);
        // the game starts in year 1, the helper starts a new year for the test cards
        $currentYear = KonjunkturphaseState::getCurrentYear($gameEvents)->value;

        expect($report->winnerId?->equals($first))->toBeTrue()
            ->and($report->getPlayer($first)->hasFinishedLebensziel)->toBeTrue()
            ->and($report->getPlayer($first)->reachedPhase)->toBe(LebenszielPhaseId::PHASE_3)
            ->and($report->getPlayer($first)->phaseReachedInYear)->toBe([1 => 1, 2 => $currentYear, 3 => $currentYear])
            ->and($report->getPlayer($first)->breakdown->get(MoneyCategory::LEBENSZIEL)->value)->toBeLessThan(0)
            ->and($report->getPlayer($second)->hasFinishedLebensziel)->toBeFalse()
            ->and($report->getPlayer($second)->reachedPhase)->toBe(LebenszielPhaseId::PHASE_1);

        $types = array_map(fn ($moment) => $moment->type, $report->keyMoments);
        expect($types)->toContain(KeyMomentType::LEBENSZIELPHASE_CHANGED)
            ->and($types)->toContain(KeyMomentType::LEBENSZIEL_FINISHED);
    });

    it('has no winner while nobody has finished the Lebensziel', function () {
        /** @var DomainTestCase $this */
        playOneYearWithMinijobs($this);

        expect(GameReportState::getGameReport($this->getGameEvents())->winnerId)->toBeNull();
    });

    it('records the Guthaben of every player after each Spielzug', function () {
        /** @var DomainTestCase $this */
        playOneYearWithMinijobs($this);
        playOneYearWithMinijobs($this);

        $gameEvents = $this->getGameEvents();
        $timeline = GameReportState::getGameReport($gameEvents)->timeline;
        $numberOfSpielzuege = $gameEvents->findAllOfType(SpielzugWasEnded::class)->count();

        foreach ($this->getPlayers() as $playerId) {
            $values = $timeline->getValuesForPlayer($playerId);
            // one value per Spielzug plus the final value
            expect($values)->toHaveCount($numberOfSpielzuege + 1)
                ->and(end($values))->toEqualWithDelta(PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->value, 0.001);
        }
        expect($timeline->getNumberOfValues())->toBe($numberOfSpielzuege + 1);
        $yearStarts = $timeline->yearStarts;
        expect($yearStarts)->toHaveCount($gameEvents->findAllOfType(KonjunkturphaseWasChanged::class)->count())
            ->and($yearStarts[0]->turnIndex)->toBe(0)
            ->and(end($yearStarts)->turnIndex)->toBe($numberOfSpielzuege);
    });

    it('counts correct moneysheet inputs and fines', function () {
        /** @var DomainTestCase $this */
        playOneYearWithMinijobs($this);

        $playerReport = GameReportState::getGameReport($this->getGameEvents())->getPlayer($this->getPlayers()[0]);

        expect($playerReport->correctMoneysheetInputs)->toBe($playerReport->moneysheetInputs)
            ->and($playerReport->breakdown->get(MoneyCategory::STRAFEN)->value)->toEqual(0);
    });

    it('highlights the player with the most money and leaves out investment highlights without trades', function () {
        /** @var DomainTestCase $this */
        playOneYearWithMinijobs($this);

        $highlights = GameReportState::getGameReport($this->getGameEvents())->highlights;
        $types = array_map(fn ($highlight) => $highlight->type, $highlights);

        expect($types)->toContain(HighlightType::MOST_MONEY)
            ->and($types)->not->toContain(HighlightType::BEST_INVESTMENT)
            ->and($types)->not->toContain(HighlightType::WORST_INVESTMENT);
    });

    it('keeps the yearly moneysheet in one sum for games of an older definitions version', function () {
        /** @var DomainTestCase $this */
        playOneYearWithMinijobs($this);
        $this->moveGameToOlderDefinitionsVersion();

        $gameEvents = $this->getGameEvents();
        $report = GameReportState::getGameReport($gameEvents);
        $breakdown = $report->getPlayer($this->getPlayers()[0])->breakdown;

        // the parts would be recalculated from the current definitions, which may have changed since
        expect($report->isFromOlderDefinitionsVersion)->toBeTrue()
            ->and($breakdown->get(MoneyCategory::JAHRESABRECHNUNG)->value)->not->toEqual(0)
            ->and($breakdown->get(MoneyCategory::LEBENSHALTUNG)->value)->toEqual(0)
            ->and($breakdown->getTotal()->value)
            ->toEqualWithDelta(PlayerState::getGuthabenForPlayer($gameEvents, $this->getPlayers()[0])->value, 0.001);
    });

    it('shows the card id for cards that no longer exist', function () {
        /** @var DomainTestCase $this */
        startYearWithOneZeitsteinPerPlayer($this);
        $player = $this->getPlayers()[0];
        $this->handle(new StartSpielzug($player));
        $this->handle(ActivateCard::create($player, CategoryId::BILDUNG_UND_KARRIERE));
        /** @var CardWasActivated $activation */
        $activation = $this->getGameEvents()->findLast(CardWasActivated::class);
        CardFinder::getInstance()->overrideCardsForTesting([]);
        CardFinder::getInstance()->overrideLegacyCardsForTesting([]);

        $expenses = GameReportState::getGameReport($this->getGameEvents())->getPlayer($player)->topCardExpenses;

        expect($expenses)->toHaveCount(1)
            ->and($expenses[0]->title)->toBe('Karte ' . $activation->cardId->value);
    });
});
