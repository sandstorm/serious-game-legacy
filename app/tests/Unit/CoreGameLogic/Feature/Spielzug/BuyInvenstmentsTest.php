<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});


describe('handleBuyInvestmentsForPlayer', function () {
    it('works as expected when buying investments', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $currentPriceLowRisk = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ);
        expect($currentPriceLowRisk)->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE));

        // buy low risk stocks
        $amountOfStocks = 100;

        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedSumOfAllStocks = new MoneyAmount($currentPriceLowRisk->value * $amountOfStocks);
        $expectedGuthaben = new MoneyAmount(Configuration::STARTKAPITAL_VALUE - Configuration::INITIAL_INVESTMENT_PRICE * $amountOfStocks);
        expect(PlayerState::getTotalValueOfAllInvestmentsForPlayer($gameEvents,
            $this->players[0]))->toEqual($expectedSumOfAllStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0],
                InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[1]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE))
            ->and(count($gameEvents->findAllOfType(ProvidesInvestmentPriceChanges::class)))->toEqual(1)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ))->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE));

        // other player does not sell any stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ))->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE))
            ->and(GamePhaseState::playerBoughtOrSoldInvestmentsThisTurn($gameEvents, $this->players[0]))->toBeTrue()
            ->and(GamePhaseState::playerBoughtOrSoldInvestmentsThisTurn($gameEvents, $this->players[1]))->toBeFalse();

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLast(SpielzugWasEnded::class)->idOfUpdatedInvestmentOrNull)->toEqual(InvestmentId::MERFEDES_PENZ)
            ->and($gameEvents->findLast(SpielzugWasEnded::class)->getLogEntry()->getText())->toEqual('beendet den Spielzug und der Kurs für Merfedes-Penz hat sich geändert')
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ)->value)->not->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            // other prices are still the same
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MEME_COIN)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BAT_COIN)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::ETF_MSCI_WORLD)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::ETF_CLEAN_ENERGY)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE);
        $currentPriceLowRisk = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ);

        // player 1 does mini job
        $this->coreGameLogic->handle(
            $this->gameId,
            DoMinijob::create($this->players[1])
        );

        // end zug for player 1
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        // after the end of spielzug for player 1, the price has not changed because no one sold/bought stocks
        expect(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ))->toEqual($currentPriceLowRisk)
            ->and(count($gameEvents->findAllOfType(ProvidesInvestmentPriceChanges::class)))->toEqual(3)
            ->and($gameEvents->findLast(SpielzugWasEnded::class)->idOfUpdatedInvestmentOrNull)->toEqual(null)
            ->and($gameEvents->findLast(SpielzugWasEnded::class)->getLogEntry()->getText())->toEqual('beendet den Spielzug')
            ->and(GamePhaseState::playerBoughtOrSoldInvestmentsThisTurn($gameEvents, $this->players[0]))->toBeFalse()
            ->and(GamePhaseState::playerBoughtOrSoldInvestmentsThisTurn($gameEvents, $this->players[1]))->toBeFalse();

        $highRiskPriceBeforeFirstBuy = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR);

        // buy some high risk stocks
        $amountOfStocksHighRisk = 50;
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::BETA_PEAR,
                $amountOfStocksHighRisk
            )
        );

        // other player does not sell any stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::BETA_PEAR)
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        // check that the prices for stocks != initial price (the calculation has random factors in it, it could happen that the price is the same as initial price, but very unlikely)
        expect(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ)->value)->not->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR)->value)->not->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            // other prices are still the same
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MEME_COIN)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BAT_COIN)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::ETF_MSCI_WORLD)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE)
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::ETF_CLEAN_ENERGY)->value)->toEqual(Configuration::INITIAL_INVESTMENT_PRICE);


        $currentPriceLowRisk = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ);
        $currentPriceHighRisk = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR);
        $expectedSumOfAllStocks = new MoneyAmount($currentPriceLowRisk->value * $amountOfStocks + $currentPriceHighRisk->value * $amountOfStocksHighRisk);
        $expectedGuthaben = $expectedGuthaben->add(
            new MoneyAmount($highRiskPriceBeforeFirstBuy->value * $amountOfStocksHighRisk * -1)
        );

        expect(PlayerState::getTotalValueOfAllInvestmentsForPlayer($gameEvents,
            $this->players[0]))->toEqual($expectedSumOfAllStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0],
                InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0],
                InvestmentId::BETA_PEAR))->toEqual($amountOfStocksHighRisk)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[1]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + 5000))
            ->and(count($gameEvents->findAllOfType(ProvidesInvestmentPriceChanges::class)))->toEqual(4);
    });

    it('throws exception if player tries to end spielzug before other players sold their investments', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                10
            )
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
    })->throws(\RuntimeException::class, 'Du kannst deinen Spielzug nicht beenden. Andere Spieler können noch Investitionen verkaufen', 1748946243);

    it('throws an exception if the player tries to buy more investments than they can afford', function () {
        $amountOfStocks = intval(Configuration::STARTKAPITAL_VALUE / Configuration::INITIAL_INVESTMENT_PRICE + 1);

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );
    })->throws(\RuntimeException::class, 'Du hast nicht genug Ressourcen', 1752066529);

    it('throws an exception if the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $this->handle(DoMinijob::create($this->players[0]));
        $this->handle(new EndSpielzug($this->players[0]));

        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        $this->handle(BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 1));
    })->throws(\RuntimeException::class, 'Cannot buy investment: Du bist insolvent', 1752066529);
});
