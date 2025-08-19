<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsToAvoidInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleSellInvestmentsForPlayer', function () {
    it('selling investments works as expected', function () {
        // buy low risk stocks
        $amountOfStocks = 100;

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedGuthaben = new MoneyAmount(Configuration::STARTKAPITAL_VALUE - Configuration::INITIAL_INVESTMENT_PRICE * $amountOfStocks);
        expect(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))
            ->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[1]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE));


        // other player does not sell any stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        // player 1 does a mini job
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
        $currentPrice = InvestmentPriceState::getCurrentInvestmentPrice($gameEvents, InvestmentId::MERFEDES_PENZ);
        // calculate expected guthaben before selling, cause the price changes after selling
        $expectedGuthaben = $expectedGuthaben->add(new MoneyAmount($currentPrice->value * $amountOfStocks));

        // player 0 sells all of their stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))
            ->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 2)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual($expectedGuthaben)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[1]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE + 5000))
            ->and(GamePhaseState::playerBoughtOrSoldInvestmentsThisTurn($gameEvents, $this->players[0]))->toBeTrue();

        // other player does not sell any stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
    });

    it('throws exception if you try to sell investments you do not have', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                100
            )
        );
    })->throws(\RuntimeException::class, 'Du hast nicht genug Investitionen vom Typ Merfedes-Penz zum Verkaufen.', 1752753850);
});

describe('handleSellInvestmentsForPlayerAfterPurchaseByAnotherPlayer', function () {
    it('selling investments after purchase works as expected', function () {
        $amountOfStocks = 100;
        // player 0 buys low risk stocks
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($gameEvents, $this->players[1]))->toBeFalse();

        // player 1 does not sell any stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($gameEvents, $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        // player 1 buys low risk stocks
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[1],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::BETA_PEAR))->toEqual(0);

        // player 0 sells half of their stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks / 2
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks / 2)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::BETA_PEAR))->toEqual(0)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[1], InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[1], InvestmentId::BETA_PEAR))->toEqual(0);
    });

    it('throws exception if player tries sell more investments than they have', function () {
        $amountOfStocks = 100;
        // player 0 buys low risk stocks
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($gameEvents, $this->players[1]))->toBeFalse();

        // player 1 does not sell any stocks
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($gameEvents, $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        // player 1 buys low risk stocks
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[1],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::BETA_PEAR))->toEqual(0)
            ->and(GamePhaseState::anotherPlayerHasInvestedThisTurn($gameEvents, $this->players[0]))->toBeTrue();

        // player 0 tries to sell more stocks than they have
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks + 50
            )
        );

    })->throws(\RuntimeException::class, 'Du hast nicht genug Investitionen vom Typ Merfedes-Penz zum Verkaufen.', 1752753850);

    it('throws exception if player tries end their turn and another player did not sell their investments', function () {
        $amountOfStocks = 100;
        // player 0 buys low risk stocks
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
    })->throws(\RuntimeException::class, 'Du kannst deinen Spielzug nicht beenden. Andere Spieler können noch Investitionen verkaufen.', 1748946243);

    it('throws exception if player tries to sell wrong type of investments another player bought', function () {
        $amountOfStocks = 100;

        // player 0 buys low risk stocks
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::MERFEDES_PENZ))->toEqual($amountOfStocks)
            ->and(PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $this->players[0], InvestmentId::BETA_PEAR))->toEqual(0)
            ->and(GamePhaseState::anotherPlayerHasInvestedThisTurn($gameEvents, $this->players[0]))->toBeFalse()
            ->and(GamePhaseState::anotherPlayerHasInvestedThisTurn($gameEvents, $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create(
                $this->players[1],
                InvestmentId::BETA_PEAR,
                50
            )
        );
    })->throws(\RuntimeException::class, 'Ein anderer Spieler muss Investitionen der gleichen Art gekauft oder verkauft haben, bevor du welche verkaufen kannst', 1752753850);

    it('throws exception if player tries to sell investments when no other player bought some', function () {
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                100
            )
        );
    })->throws(\RuntimeException::class, 'Ein anderer Spieler muss Investitionen der gleichen Art gekauft oder verkauft haben, bevor du welche verkaufen kannst', 1752753850);
});

describe('handleSellInvestmentsToAvoidInsolvenzForPlayer', function () {
    // sell investments for a player if they would need to file for Insolvenz
    it('throws an exception if player has no negative balance and therefore is not allowed to sell investments during the KonjunkturphaseChange', function () {
        /** @var TestCase $this */
        $this->handle(BuyInvestmentsForPlayer::create($this->getPlayers()[0], InvestmentId::MERFEDES_PENZ, 10));
        $this->handle(SellInvestmentsToAvoidInsolvenzForPlayer::create($this->getPlayers()[0], InvestmentId::MERFEDES_PENZ, 10));
    })->throws(\RuntimeException::class, 'Cannot sell investments for insolvenz: Dein Kontostand ist positiv', 1757078994);

    it('throws an exception if player has no investments to sell', function () {
        /** @var TestCase $this */
        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->players[0]);
        $cardsForTesting = [
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->add(new MoneyAmount(1))->negate(),
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(DoMinijob::create($this->getPlayers()[0]));
        $this->handle(SellInvestmentsToAvoidInsolvenzForPlayer::create($this->getPlayers()[0], InvestmentId::MERFEDES_PENZ, 10));
    })->throws(\RuntimeException::class, 'Cannot sell investments for insolvenz: Du hast nicht genug Investitionen vom Typ Merfedes-Penz zum Verkaufen.', 1757078994);

    it('sells investments for almost insolvent player and returns correct amount of money', function () {
        /** @var TestCase $this */
        $this->handle(BuyInvestmentsForPlayer::create($this->getPlayers()[0], InvestmentId::MERFEDES_PENZ, 10));
        $this->handle(SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create($this->getPlayers()[1], InvestmentId::MERFEDES_PENZ, 0));
        $this->handle(new EndSpielzug($this->getPlayers()[0]));

        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->players[0]);
        $cardsForTesting = [
            new MinijobCardDefinition(
                id: CardId::fromString("forTesting"),
                title: "forTesting",
                description: "forTesting",
                resourceChanges: new ResourceChanges(),
            ),
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->add(new MoneyAmount(1))->negate(),
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(DoMinijob::create($this->getPlayers()[1]));
        $this->handle(new EndSpielzug($this->getPlayers()[1]));

        $this->handle(DoMinijob::create($this->getPlayers()[0]));
        $gameEventsBeforeSellingAllInvestments = $this->getGameEvents();
        $this->handle(SellInvestmentsToAvoidInsolvenzForPlayer::create($this->getPlayers()[0], InvestmentId::MERFEDES_PENZ, 10));
        $gameEventsAfterSellingAllInvestments = $this->getGameEvents();

        $amountOfInvestmentsBeforeSellingAllInvestments = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEventsBeforeSellingAllInvestments, $this->getPlayers()[0], InvestmentId::MERFEDES_PENZ);
        $amountOfInvestmentsAfterSellingAllInvestments = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEventsAfterSellingAllInvestments, $this->getPlayers()[0], InvestmentId::MERFEDES_PENZ);
        $guthabenBeforeSellingAllInvestments = PlayerState::getGuthabenForPlayer($gameEventsBeforeSellingAllInvestments, $this->getPlayers()[0]);
        $guthabenAfterSellingAllInvestments = PlayerState::getGuthabenForPlayer($gameEventsAfterSellingAllInvestments, $this->getPlayers()[0]);
        $currentInvestmentPrice = InvestmentPriceState::getCurrentInvestmentPrice($gameEventsAfterSellingAllInvestments, InvestmentId::MERFEDES_PENZ);

        expect($amountOfInvestmentsBeforeSellingAllInvestments)->toEqual(10)
            ->and($amountOfInvestmentsAfterSellingAllInvestments)->toEqual(0)
            ->and($guthabenBeforeSellingAllInvestments->value)->toEqual(-1)
            ->and($guthabenAfterSellingAllInvestments->value)->toEqual(round(-1 + ($currentInvestmentPrice->value * 10), 1));
    });
});
