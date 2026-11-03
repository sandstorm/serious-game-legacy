<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyImmobilieForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellImmobilieForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellImmobilieForPlayerToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsToAvoidInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldImmobilieToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestmentsToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\State\TransactionHistoryState;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\ImmobilienType;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Tests\DomainTestCase;

beforeEach(function () {
    /** @var DomainTestCase $this */
    $this->setupBasicGame();
});

describe('TransactionHistoryState::getTransactionHistoryForPlayer', function () {
    it('returns empty array when no transactions exist', function () {
        /** @var DomainTestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toBeArray()->toBeEmpty();
    });

    it('records a single investment buy', function () {
        /** @var DomainTestCase $this */
        // Player 0 buys stocks
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 10)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toHaveCount(1)
            ->and($history[0]->playerTurn->value)->toBe(1)
            ->and($history[0]->assetName)->toBe('Merfedes-Penz')
            ->and($history[0]->type)->toBe('Kauf')
            ->and($history[0]->amount)->toBe(10)
            ->and($history[0]->price)->toEqual(new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE))
            ->and($history[0]->holdingAfter)->toBe(10)
            ->and($history[0]->iconClass)->toBe('icon-aktien');
    });

    it('records buy and sell with correct holding after', function () {
        /** @var DomainTestCase $this */
        // Turn 1: Player 0 buys 10 stocks
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 10)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        // Player 1 does minijob and ends turn
        $this->handle(new StartSpielzug($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Player 0 sells 3 stocks
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 3)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toHaveCount(2)
            ->and($history[0]->type)->toBe('Kauf')
            ->and($history[0]->holdingAfter)->toBe(10)
            ->and($history[0]->playerTurn->value)->toBe(1)
            ->and($history[1]->type)->toBe('Verkauf')
            ->and($history[1]->amount)->toBe(3)
            ->and($history[1]->holdingAfter)->toBe(7)
            ->and($history[1]->playerTurn->value)->toBe(2);
    });

    it('tracks holdings per investment type independently', function () {
        /** @var DomainTestCase $this */
        // Turn 1: Player 0 buys Merfedes-Penz
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 10)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        // Player 1 does minijob and ends turn
        $this->handle(new StartSpielzug($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Player 0 buys Bat-Coin
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::BAT_COIN, 20)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::BAT_COIN)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toHaveCount(2)
            ->and($history[0]->assetName)->toBe('Merfedes-Penz')
            ->and($history[0]->holdingAfter)->toBe(10)
            ->and($history[1]->assetName)->toBe('Bat-Coin')
            ->and($history[1]->holdingAfter)->toBe(20);
    });

    it('maps icon classes correctly for different investment types', function () {
        /** @var DomainTestCase $this */
        // Turn 1: Buy Aktie
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 1)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->handle(new StartSpielzug($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Buy ETF
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::ETF_MSCI_WORLD, 1)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::ETF_MSCI_WORLD)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->handle(new StartSpielzug($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 3: Buy Krypto
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::BAT_COIN, 1)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::BAT_COIN)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toHaveCount(3)
            ->and($history[0]->iconClass)->toBe('icon-aktien')
            ->and($history[1]->iconClass)->toBe('icon-ETF')
            ->and($history[2]->iconClass)->toBe('icon-krypto');
    });

    it('records immobilie buy with correct icon and asset name', function () {
        /** @var DomainTestCase $this */
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('immo1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create($this->players[0], new CardId('immo1'))
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toHaveCount(1)
            ->and($history[0]->assetName)->toBe('Immobilie: Kauf Wohnung')
            ->and($history[0]->iconClass)->toBe('icon-immobilien')
            ->and($history[0]->type)->toBe('Kauf')
            ->and($history[0]->amount)->toBe(1)
            ->and($history[0]->price)->toEqual(new MoneyAmount(20000))
            ->and($history[0]->holdingAfter)->toBe(1);
    });

    it('records several investment transactions in a row with the correct holding after each', function () {
        /** @var DomainTestCase $this */
        // turn 1: player 0 buys 10
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 10));
        $this->handle(DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ));
        $this->handle(new EndSpielzug($this->players[0]));

        // player 1 buys -> player 0 sells 2 in response
        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(BuyInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ, 1));
        $this->handle(SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 2));
        $this->handle(new EndSpielzug($this->players[1]));

        // turn 2: player 0 sells 3
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(SellInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 3));
        $this->handle(DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ));
        $this->handle(new EndSpielzug($this->players[0]));

        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // turn 3: player 0 buys 5 again
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 5));

        $history = TransactionHistoryState::getTransactionHistoryForPlayer($this->getGameEvents(), $this->players[0]);
        expect(array_map(fn ($entry) => [$entry->playerTurn->value, $entry->type, $entry->amount, $entry->holdingAfter], $history))
            ->toBe([
                [1, 'Kauf', 10, 10],
                [2, 'Verkauf', 2, 8],
                [2, 'Verkauf', 3, 5],
                [3, 'Kauf', 5, 10],
            ]);
    });

    it('records buying the same immobilie twice and selling one', function () {
        /** @var DomainTestCase $this */
        // cheap enough to buy it twice with the Startkapital
        $immobilie = new ImmobilienCardDefinition(
            id: new CardId('immo1'),
            title: 'Kauf Wohnung',
            description: 'Eine Wohnung steht zum Verkauf.',
            phaseId: LebenszielPhaseId::PHASE_1,
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-5000),
            ),
            annualRent: new MoneyAmount(1500),
            immobilienTyp: ImmobilienType::WOHNUNG
        );
        // turn 1: player 0 buys the immobilie
        $this->startNewKonjunkturphaseWithCardsOnTop([$immobilie]);
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyImmobilieForPlayer::create($this->players[0], new CardId('immo1')));
        $this->handle(new EndSpielzug($this->players[0]));
        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // turn 2: player 0 buys the same immobilie again (the card is back in the pile in the new Konjunkturphase)
        $this->startNewKonjunkturphaseWithCardsOnTop([$immobilie]);
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyImmobilieForPlayer::create($this->players[0], new CardId('immo1')));
        $this->handle(new EndSpielzug($this->players[0]));
        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // turn 3: player 0 sells the first one
        $this->handle(new StartSpielzug($this->players[0]));
        $firstImmobilieId = $this->getGameEvents()->findFirst(PlayerHasBoughtImmobilie::class)->getImmobilieId();
        $this->handle(SellImmobilieForPlayer::create($this->players[0], $firstImmobilieId));
        $this->handle(new EndSpielzug($this->players[0]));
        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // turn 4: player 0 buys investments (only one Zeitstein action per turn)
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 10));

        $gameEvents = $this->getGameEvents();
        $salePrice = $gameEvents->findLast(PlayerHasSoldImmobilie::class)->getResourceChanges($this->players[0])->guthabenChange;
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);
        expect(array_map(fn ($entry) => [$entry->playerTurn->value, $entry->assetName, $entry->type, $entry->amount, $entry->holdingAfter], $history))
            ->toBe([
                [1, 'Immobilie: Kauf Wohnung', 'Kauf', 1, 1],
                [2, 'Immobilie: Kauf Wohnung', 'Kauf', 1, 2],
                [3, 'Immobilie: Kauf Wohnung', 'Verkauf', 1, 1],
                [4, 'Merfedes-Penz', 'Kauf', 10, 10],
            ])
            ->and($salePrice->value)->toBeGreaterThan(0)
            ->and($history[2]->price)->toEqual($salePrice)
            ->and($history[2]->iconClass)->toBe('icon-immobilien');
    });

    it('records investments and immobilien sold to avoid the Insolvenz', function () {
        /** @var DomainTestCase $this */
        $immobilie = new ImmobilienCardDefinition(
            id: new CardId('immo1'),
            title: 'Kauf Wohnung',
            description: 'Eine Wohnung steht zum Verkauf.',
            phaseId: LebenszielPhaseId::PHASE_1,
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-4000),
            ),
            annualRent: new MoneyAmount(1000),
            immobilienTyp: ImmobilienType::WOHNUNG
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$immobilie]);
        // turn 1: player 0 buys investments
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::BETA_PEAR, 10));
        $this->handle(DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::BETA_PEAR));
        $this->handle(new EndSpielzug($this->players[0]));
        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // turn 2: player 0 buys an immobilie (only one Zeitstein action per turn)
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(BuyImmobilieForPlayer::create($this->players[0], new CardId('immo1')));
        $this->handle(new EndSpielzug($this->players[0]));
        $this->handle(new StartSpielzug($this->players[1]));
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));
        $immobilieId = $this->getGameEvents()->findLast(PlayerHasBoughtImmobilie::class)->getImmobilieId();

        // turn 3: player 0 gets a negative balance, then sells both to avoid the Insolvenz after their Spielzug (-> turn 4)
        // WHY: the investments are sold in two parts, so each kind of sale is followed by another entry
        $this->setupNegativeBalanceAtEndOfKonjunkturphase([$immobilie]);
        $this->handle(SellInvestmentsToAvoidInsolvenzForPlayer::create($this->players[0], InvestmentId::BETA_PEAR, 5));
        $this->handle(SellImmobilieForPlayerToAvoidInsolvenz::create($this->players[0], $immobilieId));
        $this->handle(SellInvestmentsToAvoidInsolvenzForPlayer::create($this->players[0], InvestmentId::BETA_PEAR, 5));

        $gameEvents = $this->getGameEvents();
        $receivedForInvestments = $gameEvents->findLast(PlayerHasSoldInvestmentsToAvoidInsolvenz::class)->getResourceChanges($this->players[0])->guthabenChange;
        $receivedForImmobilie = $gameEvents->findLast(PlayerHasSoldImmobilieToAvoidInsolvenz::class)->getResourceChanges($this->players[0])->guthabenChange;
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);
        expect(array_map(fn ($entry) => [$entry->playerTurn->value, $entry->assetName, $entry->type, $entry->amount, $entry->holdingAfter], $history))
            ->toBe([
                [1, 'BetaPear', 'Kauf', 10, 10],
                [2, 'Immobilie: Kauf Wohnung', 'Kauf', 1, 1],
                [4, 'BetaPear', 'Verkauf', 5, 5],
                [4, 'Immobilie: Kauf Wohnung', 'Verkauf', 1, 0],
                [4, 'BetaPear', 'Verkauf', 5, 0],
            ])
            ->and($receivedForInvestments->value)->toBeGreaterThan(0)
            // the event only knows the total, the history shows the price per investment
            ->and($history[4]->price)->toEqual(new MoneyAmount($receivedForInvestments->value / 5))
            ->and($history[4]->iconClass)->toBe('icon-aktien')
            ->and($receivedForImmobilie->value)->toBeGreaterThan(0)
            ->and($history[3]->price)->toEqual($receivedForImmobilie)
            ->and($history[3]->iconClass)->toBe('icon-immobilien');
    });

    it('only returns transactions for the requested player', function () {
        /** @var DomainTestCase $this */
        // Player 0 buys stocks
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 10)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $historyPlayer0 = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);
        $historyPlayer1 = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[1]);

        expect($historyPlayer0)->toHaveCount(1)
            ->and($historyPlayer1)->toBeEmpty();
    });

    it('records optional sell after another player buys investments', function () {
        /** @var DomainTestCase $this */
        // Turn 1: Player 0 does minijob
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        // Turn 1: Player 1 buys stocks so they have some to sell later
        $this->handle(new StartSpielzug($this->players[1]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ, 10)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Player 0 buys stocks — player 1 gets prompted to sell
        $this->handle(new StartSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 5)
        );

        // Player 1 sells 3 in response
        $this->coreGameLogic->handle(
            $this->gameId,
            SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create(
                $this->players[1],
                InvestmentId::MERFEDES_PENZ,
                3
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $historyPlayer1 = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[1]);

        expect($historyPlayer1)->toHaveCount(2)
            ->and($historyPlayer1[0]->type)->toBe('Kauf')
            ->and($historyPlayer1[0]->amount)->toBe(10)
            ->and($historyPlayer1[0]->holdingAfter)->toBe(10)
            ->and($historyPlayer1[1]->type)->toBe('Verkauf')
            ->and($historyPlayer1[1]->amount)->toBe(3)
            ->and($historyPlayer1[1]->holdingAfter)->toBe(7)
            ->and($historyPlayer1[1]->iconClass)->toBe('icon-aktien');
    });
});
