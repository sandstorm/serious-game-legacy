<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('calculate Insolvenzabgaben', function () {
    it('returns 0 if the player is not insolvent', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('job1'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(100000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('job1')));

        $gameEvents = $this->getGameEvents();
        expect(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[0])->value)->toEqual(0)
            ->and(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[1])->value)->toEqual(0);
    });

    it('returns 0 if the player is insolvent and has no income', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $gameEvents = $this->getGameEvents();
        expect(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[0])->value)->toEqual(0)
            ->and(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[1])->value)->toEqual(0);
    });

    it('returns 0 if the player is insolvent and earns less than the Pfändungsfreigrenze after taxes', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $cardsForTesting = [new JobCardDefinition(
            id: new CardId('job1'),
            title: 'offered 1',
            description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
            gehalt: new MoneyAmount(12000),
            requirements: new JobRequirements(
                zeitsteine: 1,
            ),
        )];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('job1')));

        $gameEvents = $this->getGameEvents();
        expect(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[0])->value)->toEqual(0)
            ->and(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[1])->value)->toEqual(0);
    });

    it('returns correct value if the player is insolvent and earns more than the Pfändungsfreigrenze after taxes', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $cardsForTesting = [new JobCardDefinition(
            id: new CardId('job1'),
            title: 'offered 1',
            description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
            gehalt: new MoneyAmount(100000),
            requirements: new JobRequirements(
                zeitsteine: 1,
            ),
        )];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('job1')));

        $gameEvents = $this->getGameEvents();
        expect(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[0])->value)->toEqual(65000)
            ->and(MoneySheetState::calculateInsolvenzabgabenForPlayer($gameEvents, $this->getPlayers()[1])->value)->toEqual(0);
    });

});
