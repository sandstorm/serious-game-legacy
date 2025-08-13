<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Tests\TestCase;

describe('getInvestmentPrice', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns initial stock price in first year', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ))->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE))
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR))->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE));
    });

    it('calculates stock price after konjunkturphase was changed', function () {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ))->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE))
            ->and(InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR))->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE))
            ->and(count($gameEvents->findAllOfType(ProvidesInvestmentPriceChanges::class)))->toEqual(1);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(count($gameEvents->findAllOfType(ProvidesInvestmentPriceChanges::class)))->toEqual(2);
    });

    it('works for all price functions', function () {
        for($i = 0; $i < 20; $i++) {
            $this->coreGameLogic->handle(
                $this->gameId,
                ChangeKonjunkturphase::create()
            );
            $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
            $currentStockPriceLR = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ);
            $currentStockPriceHR = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BETA_PEAR);
            $currentStockPriceEtf1 = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::ETF_CLEAN_ENERGY);
            $currentStockPriceEtf2 = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::ETF_MSCI_WORLD);
            $currentStockPriceBatCoin = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::BAT_COIN);
            $currentStockPriceMemeCoin = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MEME_COIN);

            // remove comment to debug price calculations

//            echo "Penz: " . $currentStockPriceLR . " | Beta: " . $currentStockPriceHR .
//                " | ETF1: " . $currentStockPriceEtf1 . " | ETF2: " . $currentStockPriceEtf2 .
//                " | BatCoin: " . $currentStockPriceBatCoin . " | MemeCoin: " . $currentStockPriceMemeCoin . "\n";
        }


        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(count($gameEvents->findAllOfType(ProvidesInvestmentPriceChanges::class)))->toEqual(21);
    });

});
