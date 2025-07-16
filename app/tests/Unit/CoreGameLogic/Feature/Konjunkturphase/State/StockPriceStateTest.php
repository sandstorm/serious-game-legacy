<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockPriceChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Tests\TestCase;


describe('getStockPrice', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns initial stock price in first year', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(StockPriceState::getCurrentStockPrice($gameEvents, StockType::LOW_RISK))->toEqual(new MoneyAmount(Configuration::INITIAL_STOCK_PRICE))
            ->and(StockPriceState::getCurrentStockPrice($gameEvents, StockType::HIGH_RISK))->toEqual(new MoneyAmount(Configuration::INITIAL_STOCK_PRICE));
    });

    it('calculate stock price after konjunkturphase was changed', function () {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(StockPriceState::getCurrentStockPrice($gameEvents, StockType::LOW_RISK))->toEqual(new MoneyAmount(Configuration::INITIAL_STOCK_PRICE))
            ->and(StockPriceState::getCurrentStockPrice($gameEvents, StockType::HIGH_RISK))->toEqual(new MoneyAmount(Configuration::INITIAL_STOCK_PRICE))
            ->and(count($gameEvents->findAllOfType(ProvidesStockPriceChanges::class)))->toEqual(1);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(count($gameEvents->findAllOfType(ProvidesStockPriceChanges::class)))->toEqual(2);
    });

});
