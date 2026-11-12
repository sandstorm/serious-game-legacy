<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\State;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\GameReport\Dto\CardExpense;
use Domain\CoreGameLogic\Feature\GameReport\Dto\CareerStep;
use Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport;
use Domain\CoreGameLogic\Feature\GameReport\Dto\GuthabenTimeline;
use Domain\CoreGameLogic\Feature\GameReport\Dto\InvestmentPriceHistory;
use Domain\CoreGameLogic\Feature\GameReport\Dto\InvestmentPricePoint;
use Domain\CoreGameLogic\Feature\GameReport\Dto\InvestmentResult;
use Domain\CoreGameLogic\Feature\GameReport\Dto\InvestmentTrade;
use Domain\CoreGameLogic\Feature\GameReport\Dto\KeyMoment;
use Domain\CoreGameLogic\Feature\GameReport\Dto\KeyMomentType;
use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyBreakdown;
use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyCategory;
use Domain\CoreGameLogic\Feature\GameReport\Dto\PlayerReport;
use Domain\CoreGameLogic\Feature\GameReport\Dto\YearStart;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisProfitWasReducedBecauseOfInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasRepaidForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasFiledForInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasFinishedLebensziel;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilieToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestmentsAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestmentsToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SteuernUndAbgabenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * Recaps a whole game for the report shown at the end of the game.
 *
 * Everything is calculated in one pass over the event stream. Only the yearly moneysheet booking needs the
 * existing projections, because its event stores just the net amount of the year.
 */
class GameReportState
{
    /**
     * Investment trades of at least this amount are listed as key moments.
     */
    private const float BIG_TRADE_THRESHOLD = 10000;

    private const int NUMBER_OF_TOP_CARD_EXPENSES = 3;

    public static function getGameReport(GameEvents $gameEvents): GameReport
    {
        $playerIds = GamePhaseState::getOrderedPlayers($gameEvents);
        // games of older versions would recalculate the moneysheet with definitions that may have changed since
        $canSplitMoneysheet = PreGameState::isPlayableWithCurrentDefinitions($gameEvents);

        $year = new Year(1);
        $turnIndex = 0;
        /** @var YearStart[] $yearStarts */
        $yearStarts = [];
        /** @var array<string, array<string, float>> $amounts player id => category => amount */
        $amounts = [];
        /** @var array<string, float> $guthaben */
        $guthaben = [];
        /** @var array<string, list<float>> $timeline */
        $timeline = [];
        /** @var array<string, CareerStep[]> $career */
        $career = [];
        /** @var array<string, array<string, array{amount: int, cost: float, invested: float, proceeds: float, gain: float}>> $holdings */
        $holdings = [];
        /** @var array<string, CardExpense[]> $cardExpenses */
        $cardExpenses = [];
        /** @var array<string, array<int, int>> $phaseReachedInYear */
        $phaseReachedInYear = [];
        /** @var array<string, int> $moneysheetInputs */
        $moneysheetInputs = [];
        /** @var array<string, int> $correctMoneysheetInputs */
        $correctMoneysheetInputs = [];
        /** @var array<string, int> $minijobs */
        $minijobs = [];
        /** @var KeyMoment[] $keyMoments */
        $keyMoments = [];
        /** @var array<array{playerId: PlayerId, title: string, saved: float}> $insuranceSavings */
        $insuranceSavings = [];
        /** @var array<string, list<InvestmentPricePoint>> $prices */
        $prices = [];
        /** @var array<string, list<InvestmentTrade>> $trades */
        $trades = [];
        $winnerId = null;
        $initialPrices = null;

        foreach ($playerIds as $playerId) {
            $amounts[$playerId->value] = [];
            $guthaben[$playerId->value] = 0;
            $timeline[$playerId->value] = [];
            $career[$playerId->value] = [];
            $holdings[$playerId->value] = [];
            $cardExpenses[$playerId->value] = [];
            $phaseReachedInYear[$playerId->value] = [];
            $moneysheetInputs[$playerId->value] = 0;
            $correctMoneysheetInputs[$playerId->value] = 0;
            $minijobs[$playerId->value] = 0;
        }

        foreach ($gameEvents->events as $index => $event) {
            // every event that changes the Guthaben is booked, many of them are also handled by their type below
            if ($event instanceof ProvidesResourceChanges) {
                foreach ($playerIds as $playerId) {
                    $change = $event->getResourceChanges($playerId)->guthabenChange->value;
                    if ($change === 0.0) {
                        continue;
                    }
                    $guthaben[$playerId->value] += $change;
                    if ($event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase && !$canSplitMoneysheet) {
                        $category = MoneyCategory::JAHRESABRECHNUNG->value;
                        $amounts[$playerId->value][$category] = ($amounts[$playerId->value][$category] ?? 0) + $change;
                    } elseif ($event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase) {
                        foreach (self::splitMoneysheetBooking($gameEvents, $index, $playerId, $change) as $category => $amount) {
                            $amounts[$playerId->value][$category] = ($amounts[$playerId->value][$category] ?? 0) + $amount;
                        }
                    } else {
                        $category = self::getCategoryForEvent($event)->value;
                        $amounts[$playerId->value][$category] = ($amounts[$playerId->value][$category] ?? 0) + $change;
                    }
                }
            }

            // the events below exclude each other
            if ($event instanceof KonjunkturphaseWasChanged) {
                $year = $event->year;
                $initialPrices ??= $event->investmentPrices;
                self::addPricePoints($prices, $event->investmentPrices, $turnIndex, isYearStart: true);
                $yearStarts[] = new YearStart(
                    turnIndex: $turnIndex,
                    year: $event->year,
                    type: $event->type,
                    name: self::getKonjunkturphaseName($event),
                );
                foreach ($playerIds as $playerId) {
                    $phaseReachedInYear[$playerId->value][LebenszielPhaseId::PHASE_1->value] ??= $event->year->value;
                }
            } elseif ($event instanceof SpielzugWasEnded) {
                $turnIndex++;
                self::addPricePoints($prices, $event->investmentPrices, $turnIndex, isYearStart: false);
                foreach ($playerIds as $playerId) {
                    $timeline[$playerId->value][] = $guthaben[$playerId->value];
                }
            } elseif ($event instanceof JobOfferWasAccepted) {
                $career[$event->playerId->value][] = new CareerStep(
                    year: $year,
                    title: self::getCardTitle($event->cardId),
                    gehalt: $event->gehalt,
                );
            } elseif ($event instanceof JobWasQuit) {
                $career[$event->playerId->value][] = new CareerStep(year: $year, title: 'Job gekündigt', gehalt: null);
            } elseif ($event instanceof MinijobWasDone) {
                $minijobs[$event->playerId->value]++;
            } elseif ($event instanceof SteuernUndAbgabenForPlayerWereEntered || $event instanceof LebenshaltungskostenForPlayerWereEntered) {
                $moneysheetInputs[$event->playerId->value]++;
                if ($event->wasInputCorrect()) {
                    $correctMoneysheetInputs[$event->playerId->value]++;
                }
            } elseif ($event instanceof CardWasActivated) {
                $cost = $event->getResourceChanges($event->playerId)->guthabenChange->value;
                if ($cost < 0) {
                    $cardExpenses[$event->playerId->value][] = new CardExpense(
                        title: self::getCardTitle($event->cardId),
                        amount: new MoneyAmount(-$cost),
                        year: $year,
                    );
                }
            } elseif ($event instanceof PlayerHasBoughtInvestment) {
                $investment = $event->getInvestmentId()->value;
                $paid = -$event->getResourceChanges($event->playerId)->guthabenChange->value;
                $position = $holdings[$event->playerId->value][$investment] ?? ['amount' => 0, 'cost' => 0.0, 'invested' => 0.0, 'proceeds' => 0.0, 'gain' => 0.0];
                $position['amount'] += $event->amount;
                $position['cost'] += $paid;
                $position['invested'] += $paid;
                $holdings[$event->playerId->value][$investment] = $position;
                $trades[$investment][] = new InvestmentTrade(
                    playerId: $event->playerId,
                    turnIndex: $turnIndex,
                    isSale: false,
                    amount: $event->amount,
                    price: $event->price,
                );
                if ($paid >= self::BIG_TRADE_THRESHOLD) {
                    $keyMoments[] = new KeyMoment(
                        year: $year,
                        playerId: $event->playerId,
                        type: KeyMomentType::BIG_INVESTMENT_TRADE,
                        text: sprintf(
                            'kauft %s Anteile %s zu je %s',
                            number_format($event->amount, 0, ',', '.'),
                            $investment,
                            $event->price->formatWithoutHtml()
                        ),
                        guthabenChange: new MoneyAmount(-$paid),
                    );
                }
            } elseif ($event instanceof PlayerHasSoldInvestment
                || $event instanceof PlayerHasSoldInvestmentsAfterInvestmentByAnotherPlayer
                || $event instanceof PlayerHasSoldInvestmentsToAvoidInsolvenz
            ) {
                $playerId = $event instanceof PlayerHasSoldInvestmentsToAvoidInsolvenz ? $event->getPlayerId() : $event->playerId;
                $amount = $event instanceof PlayerHasSoldInvestmentsToAvoidInsolvenz ? $event->getAmount() : $event->amount;
                $price = $event instanceof PlayerHasSoldInvestmentsToAvoidInsolvenz ? $event->getPrice() : $event->price;
                $investment = $event->getInvestmentId()->value;
                $received = $event->getResourceChanges($playerId)->guthabenChange->value;
                $position = $holdings[$playerId->value][$investment] ?? ['amount' => 0, 'cost' => 0.0, 'invested' => 0.0, 'proceeds' => 0.0, 'gain' => 0.0];
                $costOfSoldShares = $position['amount'] > 0 ? $position['cost'] / $position['amount'] * $amount : 0;
                $position['amount'] -= $amount;
                $position['cost'] -= $costOfSoldShares;
                $position['proceeds'] += $received;
                $position['gain'] += $received - $costOfSoldShares;
                $holdings[$playerId->value][$investment] = $position;
                $trades[$investment][] = new InvestmentTrade(
                    playerId: $playerId,
                    turnIndex: $turnIndex,
                    isSale: true,
                    amount: $amount,
                    price: $price,
                );
                if ($received >= self::BIG_TRADE_THRESHOLD) {
                    $keyMoments[] = new KeyMoment(
                        year: $year,
                        playerId: $playerId,
                        type: KeyMomentType::BIG_INVESTMENT_TRADE,
                        text: sprintf(
                            'verkauft %s Anteile %s zu je %s',
                            number_format($amount, 0, ',', '.'),
                            $investment,
                            new MoneyAmount($received / max(1, $amount))->formatWithoutHtml()
                        ),
                        detail: sprintf('Gewinn: %s', new MoneyAmount($received - $costOfSoldShares)->formatWithoutHtml()),
                        guthabenChange: new MoneyAmount($received),
                    );
                }
            } elseif ($event instanceof LebenszielphaseWasChanged) {
                $phaseReachedInYear[$event->playerId->value][$event->currentPhase->value] = $year->value;
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->playerId,
                    type: KeyMomentType::LEBENSZIELPHASE_CHANGED,
                    text: 'erreicht Lebenszielphase ' . $event->currentPhase->value,
                    guthabenChange: $event->getResourceChanges($event->playerId)->guthabenChange,
                );
            } elseif ($event instanceof PlayerHasFinishedLebensziel) {
                $winnerId ??= $event->playerId;
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->playerId,
                    type: KeyMomentType::LEBENSZIEL_FINISHED,
                    text: 'erreicht das Lebensziel „' . $event->lebenszielName . '“',
                    guthabenChange: $event->getResourceChanges($event->playerId)->guthabenChange,
                );
            } elseif ($event instanceof EreignisWasTriggered) {
                $definition = self::findCard($event->ereignisCardId, EreignisCardDefinition::class);
                $change = $event->getResourceChanges($event->playerId)->guthabenChange;
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->playerId,
                    type: KeyMomentType::EREIGNIS,
                    text: 'zieht das Ereignis „' . self::getCardTitle($event->ereignisCardId) . '“',
                    detail: $definition?->getDescription(),
                    guthabenChange: $change->value === 0.0 ? null : $change,
                );
                // an insurance replaces the loss of the Ereignis card
                $saved = $definition === null ? 0 : $change->value - $definition->getResourceChanges()->guthabenChange->value;
                if ($definition !== null && $saved > 0) {
                    $insuranceSavings[] = ['playerId' => $event->playerId, 'title' => $definition->getTitle(), 'saved' => $saved];
                }
            } elseif ($event instanceof PlayerHasFiledForInsolvenz) {
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->getPlayerId(),
                    type: KeyMomentType::INSOLVENZ,
                    text: 'meldet Insolvenz an',
                );
            } elseif ($event instanceof LoanWasTakenOutForPlayer) {
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->playerId,
                    type: KeyMomentType::LOAN_TAKEN,
                    text: 'nimmt einen Kredit über ' . $event->loanData->loanAmount->formatWithoutHtml() . ' auf',
                    detail: 'Rückzahlung insgesamt ' . $event->loanData->totalRepayment->formatWithoutHtml(),
                    guthabenChange: $event->loanData->loanAmount,
                );
            } elseif ($event instanceof LoanWasRepaidForPlayer) {
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->playerId,
                    type: KeyMomentType::LOAN_REPAID,
                    text: 'zahlt einen Kredit vorzeitig zurück',
                    guthabenChange: $event->getResourceChanges($event->playerId)->guthabenChange,
                );
            } elseif ($event instanceof PlayerHasBoughtImmobilie) {
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->getPlayerId(),
                    type: KeyMomentType::IMMOBILIE_BOUGHT,
                    text: 'kauft eine Immobilie: ' . self::getCardTitle($event->getCardId()),
                    guthabenChange: $event->getResourceChanges($event->getPlayerId())->guthabenChange,
                );
            } elseif ($event instanceof PlayerHasSoldImmobilie || $event instanceof PlayerHasSoldImmobilieToAvoidInsolvenz) {
                $keyMoments[] = new KeyMoment(
                    year: $year,
                    playerId: $event->getPlayerId(),
                    type: KeyMomentType::IMMOBILIE_SOLD,
                    text: 'verkauft eine Immobilie: ' . self::getCardTitle($event->getCardId()),
                    guthabenChange: $event->getResourceChanges($event->getPlayerId())->guthabenChange,
                );
            }
        }

        $playerReports = [];
        foreach ($playerIds as $playerId) {
            $timeline[$playerId->value][] = $guthaben[$playerId->value];
            $expenses = $cardExpenses[$playerId->value];
            usort($expenses, fn (CardExpense $a, CardExpense $b) => $b->amount->value <=> $a->amount->value);

            $playerReports[] = new PlayerReport(
                playerId: $playerId,
                name: PlayerState::getNameForPlayer($gameEvents, $playerId),
                colorClass: PlayerState::getPlayerColorClass($gameEvents, $playerId),
                lebenszielName: PlayerState::getLebenszielDefinitionForPlayerOrNull($gameEvents, $playerId)?->name,
                reachedPhase: PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId),
                phaseReachedInYear: $phaseReachedInYear[$playerId->value],
                hasFinishedLebensziel: $winnerId !== null && $winnerId->equals($playerId),
                finalGuthaben: new MoneyAmount($guthaben[$playerId->value]),
                assetsValue: self::getAssetsValue($gameEvents, $playerId),
                openLoanRepayments: MoneySheetState::getTotalOpenRepaymentValueForAllLoans($gameEvents, $playerId),
                breakdown: new MoneyBreakdown($amounts[$playerId->value]),
                career: $career[$playerId->value],
                investmentResults: self::toInvestmentResults($gameEvents, $holdings[$playerId->value]),
                topCardExpenses: array_slice($expenses, 0, self::NUMBER_OF_TOP_CARD_EXPENSES),
                moneysheetInputs: $moneysheetInputs[$playerId->value],
                correctMoneysheetInputs: $correctMoneysheetInputs[$playerId->value],
                numberOfMinijobs: $minijobs[$playerId->value],
            );
        }

        return new GameReport(
            winnerId: $winnerId,
            players: $playerReports,
            timeline: new GuthabenTimeline($timeline, $yearStarts),
            investmentPrices: new InvestmentPriceHistory($prices, $trades, $turnIndex),
            keyMoments: $keyMoments,
            highlights: GameReportHighlights::find($gameEvents, $playerReports, $insuranceSavings, $initialPrices ?? []),
            isFromOlderDefinitionsVersion: !$canSplitMoneysheet,
        );
    }

    /**
     * @param array<string, list<InvestmentPricePoint>> $prices
     * @param InvestmentPrice[] $investmentPrices
     */
    private static function addPricePoints(array &$prices, array $investmentPrices, int $turnIndex, bool $isYearStart): void
    {
        foreach ($investmentPrices as $investmentPrice) {
            $prices[$investmentPrice->investmentId->value][] = new InvestmentPricePoint(
                turnIndex: $turnIndex,
                isYearStart: $isYearStart,
                price: $investmentPrice->price,
            );
        }
    }

    /**
     * Splits the net amount of the yearly moneysheet into its parts. They are calculated from the events before the
     * booking, exactly as {@see MoneySheetState::calculateTotalForPlayer()} did when the moneysheet was completed.
     *
     * @return array<string, float> category => amount
     */
    private static function splitMoneysheetBooking(GameEvents $gameEvents, int $index, PlayerId $playerId, float $total): array
    {
        $eventsBeforeBooking = GameEvents::fromArray(array_slice($gameEvents->events, 0, $index));
        $parts = [
            MoneyCategory::GEHALT->value => PlayerState::getCurrentGehaltForPlayer($eventsBeforeBooking, $playerId)->value,
            MoneyCategory::KAPITALERTRAEGE->value => MoneySheetState::getAnnualIncomeForAllInvestments($eventsBeforeBooking, $playerId)->value,
            MoneyCategory::STEUERN->value => -MoneySheetState::calculateSteuernUndAbgabenForPlayer($eventsBeforeBooking, $playerId)->value,
            MoneyCategory::LEBENSHALTUNG->value => -MoneySheetState::calculateLebenshaltungskostenForPlayer($eventsBeforeBooking, $playerId)->value,
            MoneyCategory::KREDITTILGUNG->value => -MoneySheetState::getAnnualExpensesForAllLoans($eventsBeforeBooking, $playerId)->value,
            MoneyCategory::VERSICHERUNGEN->value => -MoneySheetState::getCostOfAllInsurances($eventsBeforeBooking, $playerId)->value,
            MoneyCategory::INSOLVENZ->value => -MoneySheetState::calculateInsolvenzabgabenForPlayer($eventsBeforeBooking, $playerId)->value,
        ];
        // keeps the report balanced, should always be 0
        $parts[MoneyCategory::SONSTIGES->value] = $total - array_sum($parts);
        return $parts;
    }

    private static function getCategoryForEvent(GameEventInterface $event): MoneyCategory
    {
        return match (true) {
            $event instanceof PreGameStarted => MoneyCategory::STARTKAPITAL,
            $event instanceof PlayerHasStartedKonjunkturphase => MoneyCategory::KONJUNKTUR,
            $event instanceof CardWasActivated => match ($event->getCategoryId()) {
                CategoryId::BILDUNG_UND_KARRIERE => MoneyCategory::BILDUNG_UND_KARRIERE,
                CategoryId::SOZIALES_UND_FREIZEIT => MoneyCategory::FREIZEIT_UND_SOZIALES,
                default => MoneyCategory::SONSTIGES,
            },
            $event instanceof MinijobWasDone => MoneyCategory::MINIJOBS,
            $event instanceof EreignisWasTriggered => MoneyCategory::EREIGNISSE,
            $event instanceof EreignisProfitWasReducedBecauseOfInsolvenz,
            $event instanceof PlayerHasFiledForInsolvenz => MoneyCategory::INSOLVENZ,
            $event instanceof PlayerHasBoughtInvestment,
            $event instanceof PlayerHasBoughtImmobilie => MoneyCategory::KAUF_VON_ANLAGEN,
            $event instanceof PlayerHasSoldInvestment,
            $event instanceof PlayerHasSoldInvestmentsAfterInvestmentByAnotherPlayer,
            $event instanceof PlayerHasSoldInvestmentsToAvoidInsolvenz,
            $event instanceof PlayerHasSoldImmobilie,
            $event instanceof PlayerHasSoldImmobilieToAvoidInsolvenz => MoneyCategory::VERKAUF_VON_ANLAGEN,
            $event instanceof LoanWasTakenOutForPlayer => MoneyCategory::KREDITE,
            $event instanceof LoanWasRepaidForPlayer => MoneyCategory::KREDITTILGUNG,
            $event instanceof InsuranceForPlayerWasConcluded,
            $event instanceof InsuranceForPlayerWasCancelled => MoneyCategory::VERSICHERUNGEN,
            $event instanceof LebenszielphaseWasChanged,
            $event instanceof PlayerHasFinishedLebensziel => MoneyCategory::LEBENSZIEL,
            $event instanceof SteuernUndAbgabenForPlayerWereCorrected,
            $event instanceof LebenshaltungskostenForPlayerWereCorrected => MoneyCategory::STRAFEN,
            default => MoneyCategory::SONSTIGES,
        };
    }

    /**
     * @param array<string, array{amount: int, cost: float, invested: float, proceeds: float, gain: float}> $holdings
     * @return InvestmentResult[]
     */
    private static function toInvestmentResults(GameEvents $gameEvents, array $holdings): array
    {
        $results = [];
        foreach ($holdings as $investment => $position) {
            $investmentId = InvestmentId::from($investment);
            $results[] = new InvestmentResult(
                investmentId: $investmentId,
                invested: new MoneyAmount($position['invested']),
                proceeds: new MoneyAmount($position['proceeds']),
                realizedGain: new MoneyAmount($position['gain']),
                amountHeld: $position['amount'],
                valueHeld: new MoneyAmount(
                    $position['amount'] * InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, $investmentId)->value
                ),
            );
        }
        return $results;
    }

    /**
     * Cards of games from older versions may have been removed from the definitions.
     */
    private static function getCardTitle(CardId $cardId): string
    {
        return self::findCard($cardId)?->getTitle() ?? 'Karte ' . $cardId->value;
    }

    /**
     * @template T of CardDefinition
     * @param class-string<T> $classString
     * @return T|null
     */
    private static function findCard(CardId $cardId, string $classString = CardDefinition::class): ?CardDefinition
    {
        try {
            return CardFinder::getInstance()->getCardById($cardId, $classString);
        } catch (\RuntimeException) {
            return null;
        }
    }

    private static function getKonjunkturphaseName(KonjunkturphaseWasChanged $event): string
    {
        try {
            return KonjunkturphaseFinder::findKonjunkturphaseById($event->id)->name;
        } catch (\InvalidArgumentException) {
            return $event->type->value;
        }
    }

    /**
     * Null if the current price of an asset is unknown, e.g. for an Immobilie that was removed from the definitions.
     */
    private static function getAssetsValue(GameEvents $gameEvents, PlayerId $playerId): ?MoneyAmount
    {
        try {
            return PlayerState::getTotalValueOfAllAssetsForPlayer($gameEvents, $playerId);
        } catch (\RuntimeException) {
            return null;
        }
    }
}
