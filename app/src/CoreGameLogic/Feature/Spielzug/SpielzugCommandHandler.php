<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\AcceptJobOfferAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ActivateCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\BuyStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CancelInsuranceForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ChangeLebenszielphaseAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CompleteMoneySheetForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ConcludeInsuranceForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\DontSellStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\PutCardBackOnTopOfPileAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\DoMinijobAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\EndSpielzugAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\EnterLebenshaltungskostenForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\EnterSteuernUndAbgabenForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\MarkPlayerAsReadyForKonjunkturphaseChangeAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\QuitJobAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SkipCardAktion as SkipCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartKonjunkturphaseForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartSpielzugAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartWeiterbildungAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SubmitAnswerForWeiterbildungAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\TakeOutALoanForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\PutCardBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SubmitAnswerForWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class SpielzugCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof AcceptJobOffer
            || $command instanceof ActivateCard
            || $command instanceof CompleteMoneysheetForPlayer
            || $command instanceof EndSpielzug
            || $command instanceof StartSpielzug
            || $command instanceof MarkPlayerAsReadyForKonjunkturphaseChange
            || $command instanceof DoMiniJob
            || $command instanceof SkipCard
            || $command instanceof StartKonjunkturphaseForPlayer
            || $command instanceof EnterSteuernUndAbgabenForPlayer
            || $command instanceof EnterLebenshaltungskostenForPlayer
            || $command instanceof ConcludeInsuranceForPlayer
            || $command instanceof CancelInsuranceForPlayer
            || $command instanceof TakeOutALoanForPlayer
            || $command instanceof ChangeLebenszielphase
            || $command instanceof QuitJob
            || $command instanceof BuyStocksForPlayer
            || $command instanceof SellStocksForPlayer
            || $command instanceof DontSellStocksForPlayer
            || $command instanceof PutCardBackOnTopOfPile
            || $command instanceof StartWeiterbildung
            || $command instanceof SubmitAnswerForWeiterbildung;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            SkipCard::class => $this->handleSkipCard($command, $gameEvents),
            ActivateCard::class => $this->handleActivateCard($command, $gameEvents),
            AcceptJobOffer::class => $this->handleAcceptJobOffer($command, $gameEvents),
            EndSpielzug::class => $this->handleEndSpielzug($command, $gameEvents),
            StartSpielzug::class => $this->handleStartSpielzug($command, $gameEvents),
            CompleteMoneysheetForPlayer::class => $this->handleCompleteMoneysheetForPlayer($command, $gameEvents),
            MarkPlayerAsReadyForKonjunkturphaseChange::class => $this->handleMarkPlayerAsReadyForKonjunkturphaseChange($command,
                $gameEvents),
            StartKonjunkturphaseForPlayer::class => $this->handleStartKonjunkturphaseForPlayer($command, $gameEvents),
            EnterSteuernUndAbgabenForPlayer::class => $this->handleEnterSteuernUndAbgabenForPlayer(
                $command, $gameEvents),
            EnterLebenshaltungskostenForPlayer::class => $this->handleEnterLebenshaltungskostenForPlayer(
                $command, $gameEvents),
            ConcludeInsuranceForPlayer::class => $this->handleConcludeInsuranceForPlayer(
                $command, $gameEvents),
            CancelInsuranceForPlayer::class => $this->handleCancelInsuranceForPlayer(
                $command, $gameEvents),
            TakeOutALoanForPlayer::class => $this->handleTakeOutALoanForPlayer(
                $command, $gameEvents),
            DoMinijob::class => $this->handleDoMinijob
                ($command, $gameEvents),
            QuitJob::class => $this->handleQuitJob
                ($command, $gameEvents),
            BuyStocksForPlayer::class => $this->handleBuyStocks($command, $gameEvents),
            SellStocksForPlayer::class => $this->handleSellStocks($command, $gameEvents),
            DontSellStocksForPlayer::class => $this->handleDontSellStocks($command, $gameEvents),
            ChangeLebenszielphase::class => $this->handleLebenszielphase($command, $gameEvents),
            PutCardBackOnTopOfPile::class => $this->handlePutCardBackOnTopOfPile($command, $gameEvents),
            StartWeiterbildung::class => $this->handleStartWeiterbildung($command, $gameEvents),
            SubmitAnswerForWeiterbildung::class => $this->handleSubmitAnswerWeiterbildung($command, $gameEvents),
        };
    }

    private function handleLebenszielphase(ChangeLebenszielphase $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new ChangeLebenszielphaseAktion();
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleQuitJob(QuitJob $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new QuitJobAktion();
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleDoMinijob(DoMinijob $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new DoMinijobAktion();
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleEndSpielzug(EndSpielzug $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $endSpielzugAktion = new EndSpielzugAktion();
        $eventsToPersist = $endSpielzugAktion->execute($command->player, $gameEvents);
        if (KonjunkturphaseState::isConditionForEndOfKonjunkturphaseMet($gameEvents)) {
            return $eventsToPersist->withAppendedEvents(
                new KonjunkturphaseHasEnded(KonjunkturphaseState::getCurrentYear($gameEvents)),
            );
        }
        return $eventsToPersist;
    }

    private function handleStartSpielzug(StartSpielzug $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $startSpielzugAktion = new StartSpielzugAktion();
        return $startSpielzugAktion->execute($command->player, $gameEvents);
    }

    private function handleAcceptJobOffer(AcceptJobOffer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new AcceptJobOfferAktion($command->jobId);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleActivateCard(
        ActivateCard $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new ActivateCardAktion($command->categoryId);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleSkipCard(SkipCard $command, GameEvents $gameState): GameEventsToPersist
    {
        $aktion = new SkipCardAktion($command->categoryId);
        return $aktion->execute($command->playerId, $gameState);
    }

    private function handleStartKonjunkturphaseForPlayer(
        StartKonjunkturphaseForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new StartKonjunkturphaseForPlayerAktion();
        return $aktion->execute(
            playerId: $command->playerId,
            gameEvents: $gameEvents,
        );
    }

    private function handleCompleteMoneysheetForPlayer(
        CompleteMoneysheetForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new CompleteMoneySheetForPlayerAktion();
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleMarkPlayerAsReadyForKonjunkturphaseChange(
        MarkPlayerAsReadyForKonjunkturphaseChange $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new MarkPlayerAsReadyForKonjunkturphaseChangeAktion();
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleEnterSteuernUndAbgabenForPlayer(
        EnterSteuernUndAbgabenForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new EnterSteuernUndAbgabenForPlayerAktion($command->input);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleEnterLebenshaltungskostenForPlayer(
        EnterLebenshaltungskostenForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new EnterLebenshaltungskostenForPlayerAktion($command->input);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleConcludeInsuranceForPlayer(
        ConcludeInsuranceForPlayer $command,
        GameEvents                 $gameEvents
    ): GameEventsToPersist {
        $aktion = new ConcludeInsuranceForPlayerAktion($command->insuranceId);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleCancelInsuranceForPlayer(
        CancelInsuranceForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new CancelInsuranceForPlayerAktion($command->insuranceId);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleTakeOutALoanForPlayer(TakeOutALoanForPlayer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new TakeOutALoanForPlayerAktion(
            $command->takeOutLoanForm
        );
        return $aktion->execute($command->playerId, $gameEvents);
    }

    /**
     * @param BuyStocksForPlayer $command
     * @param GameEvents $gameEvents
     * @return GameEventsToPersist
     */
    private function handleBuyStocks(BuyStocksForPlayer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new BuyStocksForPlayerAktion(
            $command->stockType,
            StockPriceState::getCurrentStockPrice($gameEvents, $command->stockType),
            $command->amount,
        );
        return $aktion->execute($command->playerId, $gameEvents);
    }

    /**
     * @param SellStocksForPlayer $command
     * @param GameEvents $gameEvents
     * @return GameEventsToPersist
     */
    private function handleSellStocks(SellStocksForPlayer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new SellStocksForPlayerAktion(
            $command->stockType,
            StockPriceState::getCurrentStockPrice($gameEvents, $command->stockType),
            $command->amount,
        );
        return $aktion->execute($command->playerId, $gameEvents);
    }

    /**
     * @param DontSellStocksForPlayer $command
     * @param GameEvents $gameEvents
     * @return GameEventsToPersist
     */
    private function handleDontSellStocks(DontSellStocksForPlayer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new DontSellStocksForPlayerAktion($command->stockType);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    /**
     * @param PutCardBackOnTopOfPile $command
     * @param GameEvents $gameEvents
     * @return GameEventsToPersist
     */
    private function handlePutCardBackOnTopOfPile(PutCardBackOnTopOfPile $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new PutCardBackOnTopOfPileAktion($command->categoryId);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleSubmitAnswerWeiterbildung(SubmitAnswerForWeiterbildung $command, GameEvents $gameEvents):GameEventsToPersist
    {
        $aktion = new SubmitAnswerForWeiterbildungAktion($command->selectedAnswer);
        return $aktion->execute($command->playerId, $gameEvents);
    }

    private function handleStartWeiterbildung(StartWeiterbildung $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new StartWeiterbildungAktion();
        return $aktion->execute($command->playerId, $gameEvents);
    }
}
