<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyImmobilieForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\TransactionHistoryState;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\ImmobilienType;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('TransactionHistoryState::getTransactionHistoryForPlayer', function () {
    it('returns empty array when no transactions exist', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toBeArray()->toBeEmpty();
    });

    it('records a single investment buy', function () {
        /** @var TestCase $this */
        // Player 0 buys stocks
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
        /** @var TestCase $this */
        // Turn 1: Player 0 buys 10 stocks
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
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Player 0 sells 3 stocks
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
        /** @var TestCase $this */
        // Turn 1: Player 0 buys Merfedes-Penz
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
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Player 0 buys Bat-Coin
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
        /** @var TestCase $this */
        // Turn 1: Buy Aktie
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::MERFEDES_PENZ, 1)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::MERFEDES_PENZ)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 2: Buy ETF
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyInvestmentsForPlayer::create($this->players[0], InvestmentId::ETF_MSCI_WORLD, 1)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            DontSellInvestmentsForPlayer::create($this->players[1], InvestmentId::ETF_MSCI_WORLD)
        );
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[1]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // Turn 3: Buy Krypto
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
        /** @var TestCase $this */
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

        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create($this->players[0], new CardId('immo1'))
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $history = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $this->players[0]);

        expect($history)->toHaveCount(1)
            ->and($history[0]->assetName)->toContain('Immobilie:')
            ->and($history[0]->assetName)->toContain('Kauf Wohnung')
            ->and($history[0]->iconClass)->toBe('icon-immobilien')
            ->and($history[0]->type)->toBe('Kauf')
            ->and($history[0]->amount)->toBe(1)
            ->and($history[0]->price)->toEqual(new MoneyAmount(20000))
            ->and($history[0]->holdingAfter)->toBe(1);
    });

    it('only returns transactions for the requested player', function () {
        /** @var TestCase $this */
        // Player 0 buys stocks
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
        /** @var TestCase $this */
        // Turn 1: Player 0 does minijob
        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        // Turn 1: Player 1 buys stocks so they have some to sell later
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
