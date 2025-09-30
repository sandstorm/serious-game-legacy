<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyImmobilieForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellImmobilieForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellImmobilieForPlayerToAvoidInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\ImmobilienType;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleBuyImmoblie', function () {
    it('works if the player has enough resources', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('inv1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
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
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('inv1'),
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - 20000))
            ->and(PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[0]))->toHaveCount(1)
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(1500))
            ->and(PlayerState::getTotalValueOfAllImmobilienForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(20000))
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toEqual($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1)
            ->and(PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[1]))->toHaveCount(0)
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[1]))->toEqual(new MoneyAmount(0))
            ->and(PlayerState::getTotalValueOfAllImmobilienForPlayer($gameEvents, $this->players[1]))->toEqual(new MoneyAmount(0))
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[1]))->toEqual($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));
    });

    it('works if a player buys the same immobilie twice and sells one', function () {
        /** @var TestCase $this */
        $purchasePrice = 5000;
        $cardForTesting = new ImmobilienCardDefinition(
            id: new CardId('inv1'),
            title: 'Kauf Wohnung',
            description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
            phaseId: LebenszielPhaseId::PHASE_1,
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount($purchasePrice * -1),
            ),
            annualRent: new MoneyAmount(1500),
            immobilienTyp: ImmobilienType::WOHNUNG
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardForTesting]);

        // player 0 buys the immobile
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('inv1'),
            )
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

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
        /** @var PlayerHasBoughtImmobilie $boughtEvent */
        $boughtEvent = $gameEvents->findLast(PlayerHasBoughtImmobilie::class);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - $purchasePrice))
            ->and($boughtEvent->getImmobilieId())->toEqual(new ImmobilieId(new CardId('inv1'), new PlayerTurn(1)))
            ->and(PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[0]))->toHaveCount(1)
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(1500))
            ->and(PlayerState::getTotalValueOfAllImmobilienForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount($purchasePrice))
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toEqual($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);

        $this->startNewKonjunkturphaseWithCardsOnTop([$cardForTesting]);

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $valueOfFirstImmobilie = ImmobilienPriceState::getCurrentPriceForImmobilie($gameEvents, $boughtEvent->getImmobilieId());

        // player 0 buys the immobile again (card was reshuffled back into the pile)
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('inv1'),
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var PlayerHasBoughtImmobilie $boughtEvent */
        $boughtEvent = $gameEvents->findLast(PlayerHasBoughtImmobilie::class);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - $purchasePrice - $purchasePrice))
            ->and($boughtEvent->getImmobilieId())->toEqual(new ImmobilieId(new CardId('inv1'), new PlayerTurn(2)))
            ->and(PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[0]))->toHaveCount(2)
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(3000))
            ->and(PlayerState::getTotalValueOfAllImmobilienForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount($valueOfFirstImmobilie->value + $purchasePrice))
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toEqual($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

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

        // player 0 sells the last immobilie bought
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var PlayerHasBoughtImmobilie $boughtEvent */
        $boughtEvent = $gameEvents->findLast(PlayerHasBoughtImmobilie::class);
        $this->coreGameLogic->handle(
            $this->gameId,
            SellImmobilieForPlayer::create(
                $this->players[0],
                $boughtEvent->getImmobilieId(),
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $immoblienOwned = PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[0]);
        expect($immoblienOwned)->toHaveCount(1)
            ->and($immoblienOwned[0]->getImmobilieId())->toEqual(new ImmobilieId(new CardId('inv1'), new PlayerTurn(1)))
            ->and($boughtEvent->getImmobilieId())->toEqual(new ImmobilieId(new CardId('inv1'), new PlayerTurn(2)))
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(1500))
            ->and(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - $purchasePrice));
    });

    it('throws exception if player tries to buy an immobile they cannot afford', function () {
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('inv1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(Configuration::STARTKAPITAL_VALUE + 20000)->negate(),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('inv1'),
            )
        );
    })->throws(\RuntimeException::class, 'Du hast nicht genug Ressourcen', 1754661378);

    it('throws exception if player tries to buy an immobile that\'s not for sale', function () {
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('inv_t1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
            new ImmobilienCardDefinition(
                id: new CardId('inv_t2'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
            new ImmobilienCardDefinition(
                id: new CardId('inv_t3'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('inv_t3'),
            )
        );
    })->throws(\RuntimeException::class, 'Diese Immobilie steht aktuell nicht zum Verkauf', 1754661378);
});

describe('handleSellImmobilie', function () {
    it('can sell Immobilie', function () {
        /** @var TestCase $this */
        $purchasePrice = 20000;
        $cardForTesting = new ImmobilienCardDefinition(
            id: new CardId('inv1'),
            title: 'Kauf Wohnung',
            description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
            phaseId: LebenszielPhaseId::PHASE_1,
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount($purchasePrice * -1),
            ),
            annualRent: new MoneyAmount(1500),
            immobilienTyp: ImmobilienType::WOHNUNG
        );

        $this->startNewKonjunkturphaseWithCardsOnTop([$cardForTesting]);

        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('inv1'),
            )
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

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
        /** @var PlayerHasBoughtImmobilie $boughtEvent */
        $boughtEvent = $gameEvents->findLast(PlayerHasBoughtImmobilie::class);

        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - $purchasePrice))
            ->and(PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[0]))->toHaveCount(1)
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(1500));

        // start new konjunkturphase to get a new price for the immobile
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardForTesting]);

        $currentPriceForImmobilie = ImmobilienPriceState::getCurrentPriceForImmobilie($this->coreGameLogic->getGameEvents($this->gameId), $boughtEvent->getImmobilieId());
        expect($currentPriceForImmobilie->value)->not->toEqual($purchasePrice);

        // now sell the immobile
        $this->coreGameLogic->handle(
            $this->gameId,
            SellImmobilieForPlayer::create(
                $this->players[0],
                $boughtEvent->getImmobilieId(),
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE - $purchasePrice + $currentPriceForImmobilie->value))
            ->and(PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->players[0]))->toHaveCount(0)
            ->and(PlayerState::getAnnualRentIncomeForPlayer($gameEvents, $this->players[0]))->toEqual(new MoneyAmount(0));
    });

    it('throws an error if the player tries to sell an immobilie that is not for sale', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('inv1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            )
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // try to sell a immobilie the player does not own
        $this->coreGameLogic->handle(
            $this->gameId,
            SellImmobilieForPlayer::create(
                $this->players[0],
                new ImmobilieId(
                    new CardId('inv1'),
                    new PlayerTurn(0)
                )
            )
        );
    })->throws(\RuntimeException::class, 'Diese Immobilie befindet sich nicht in deinem Besitz', 1754909475);
});

describe('Sell Immoblien to Avoid insolvenz', function () {
    it('throws an error if the player is not insolvent', function () {
        /** @var TestCase $this */
        $purchasePrice = 2000;
        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('immo1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount($purchasePrice)->negate(),
                ),
                annualRent: new MoneyAmount(1500),
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
            new MinijobCardDefinition(
                id: CardId::fromString("forTesting"),
                title: "forTesting",
                description: "forTesting",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                )
            ),
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    /* guthabenChange: $initialGuthaben->negate(), */
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 2,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $turn = PlayerState::getCurrentTurnForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('immo1'),
            )
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

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

        // player 0 does mini job
        $this->coreGameLogic->handle(
            $this->gameId,
            DoMinijob::create($this->players[0])
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $lebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $this->handle(EnterLebenshaltungskostenForPlayer::create($this->getPlayers()[0], $lebenshaltungskosten));
        $this->handle(CompleteMoneysheetForPlayer::create($this->getPlayers()[0]));
        $this->handle(
            SellImmobilieForPlayerToAvoidInsolvenz::create(
                $this->getPlayers()[0],
                new ImmobilieId(new CardId('immo1'), $turn)
            )
        );
    })->throws(\RuntimeException::class, 'Dein Kontostand ist positiv', 1754909475);

    it('returns 80 percent of the purchase price if the player has a negative balance and has an immobilie to sell', function () {
        /** @var TestCase $this */
        // purchasePrice and rent amount to 5000 (which is the same as the default Lebenshaltungskosten)
        // they will get added to the Guthaben at the end of the Konjunkturphase/after selling the immobilie
        // so at the end the player should come out at 0.
        $purchasePrice = new MoneyAmount(4000);
        $rent = new MoneyAmount(1000);
        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $cardsForTesting = [
            new ImmobilienCardDefinition(
                id: new CardId('immo1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: $purchasePrice->negate(),
                ),
                annualRent: $rent,
                immobilienTyp: ImmobilienType::WOHNUNG
            ),
            new MinijobCardDefinition(
                id: CardId::fromString("forTesting"),
                title: "forTesting",
                description: "forTesting",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                )
            ),
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->negate()
                        ->subtract($rent)
                        ->add(new MoneyAmount($purchasePrice->value * 0.2)) // selling the Immobilie will not return the full price
                        ->add(MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[0])),
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 2,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $turn = PlayerState::getCurrentTurnForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyImmobilieForPlayer::create(
                $this->players[0],
                new CardId('immo1'),
            )
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

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

        // player 0 does mini job
        $this->coreGameLogic->handle(
            $this->gameId,
            DoMinijob::create($this->players[0])
        );

        // end zug for player 0
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $gameEvents = $this->getGameEvents();
        $guthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->getPlayers()[0]);
        expect($guthaben->value)->toEqual(800);

        $lebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $this->handle(EnterLebenshaltungskostenForPlayer::create($this->getPlayers()[0], $lebenshaltungskosten));
        $this->handle(CompleteMoneysheetForPlayer::create($this->getPlayers()[0]));
        $this->handle(
            SellImmobilieForPlayerToAvoidInsolvenz::create(
                $this->getPlayers()[0],
                new ImmobilieId(new CardId('immo1'), $turn)
            )
        );

        $gameEvents = $this->getGameEvents();
        $guthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->getPlayers()[0]);
        $immobilien = PlayerState::getImmoblienOwnedByPlayer($gameEvents, $this->getPlayers()[0]);
        expect($guthaben->value)->toEqual(0)
            ->and(count($immobilien))->toBe(0);
    });
});
