<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet\State;

use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\ComponentWithForm;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('calculateLebenshaltungskostenForPlayer', function () {
    it('returns 5000 when player has no job', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toEqual(new MoneyAmount(5000));
    });

    it('returns 5000 when 35% of the Gehalt is less than 5000', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(14000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toEqual(new MoneyAmount(5000));
    });

    it('returns 35% of the Gehalt it that is more than 5000', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toEqual(new MoneyAmount(11900));
    });
});

describe('calculateSteuernUndAbgabenForPlayer', function () {
    it('returns 0 when player has no job', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toEqual(new MoneyAmount(0));
    });

    it('returns 35% of the Gehalt if the player has a job', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('tj0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toEqual(new MoneyAmount(8500));
    });
});

describe('getNumberOfTriesForSteuernUndAbgabenInput', function () {
    it('returns 0 at the start of the game', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameEvents, $this->players[0]))->toBe(0);
    });

    it('returns the correct amount after trying', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameEvents, $this->players[0]))->toBe(1);
    });

    it('resets tries when an event happens which changes the calculation', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameEvents, $this->players[0]))->toBe(1);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameEvents, $this->players[0]))->toBe(0);
    });

});

describe('getResultOfLastSteuernUndAbgabenInput', function () {
    it('works if no input has happened yet', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeTrue()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was successful', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeTrue()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong once', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeFalse()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong twice', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3000)));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3080)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeFalse()
            ->and($actual->fine)->toEqual(new MoneyAmount(250));
    });
});

describe('getResultOfLastLebenshaltungskostenInput', function () {
    it('works if no input has happened yet', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastLebenshaltungskostenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeTrue()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was successful', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(5000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastLebenshaltungskostenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeTrue()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong once', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastLebenshaltungskostenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeFalse()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong twice', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3000)));
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3080)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastLebenshaltungskostenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeFalse()
            ->and($actual->fine)->toEqual(new MoneyAmount(250));
    });
});

describe('getLastInputForSteuernUndAbgaben', function () {
    it('returns 0 if no input happened yet', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(0));
    });

    it('returns correct input after correct player input', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(0));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(8500)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(8500));
    });

    it('returns correct input after one incorrect player input', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(2000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(2000));
    });

    it('returns correct input after two incorrect player inputs', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(2000)));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(0)); // expect corrected value
    });
});

describe('getLastInputLebenshaltungskosten', function () {
    it('returns 0 if no input happened yet', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(0));
    });

    it('returns correct input after correct player input', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(5000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(5000));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(11900)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(11900));
    });

    it('returns correct input after one incorrect player input', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(2000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(2000));
    });

    it('returns correct input after two incorrect player inputs', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(2000)));
        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(5000)); // expect corrected value
    });
});

describe('doesSteuernUndAbgabenRequirePlayerAction', function () {
    it('returns false if no input happened', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeFalse();
    });

    it('returns false if the last input was correct', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeFalse();

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId("j0")));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(8500)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeFalse();
    });

    it('returns true if the last input was incorrect', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(10)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId("j0")));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(400)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeTrue();
    });


    it('returns false if the last two inputs were incorrect and the value was corrected for the player', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(10)));
        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(400)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeFalse();
    });

    it('returns true if the last input was correct but the Gehalt changed since then', function () {
        /** @var TestCase $this */
        $testCards = [
            new JobCardDefinition(
                id: new CardId('j0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($testCards);

        $this->coreGameLogic->handle($this->gameId,
            EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeFalse();

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId("j0")));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $this->players[0]))->toBeTrue();
    });
});

describe('doesPlayerHaveThisInsurance', function () {
    it('returns false if no insurance was concluded', function () {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[2]->id))->toBeFalse();
    });

    it('returns true if insurance was concluded', function () {
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[2]->id))->toBeFalse();
    });

    it('returns false if insurance was cancelled', function () {
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[2]->id))->toBeFalse();

        $this->coreGameLogic->handle($this->gameId,
            CancelInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[2]->id))->toBeFalse();
    });

    it('returns true if insurance was concluded, cancelled and concluded again', function () {
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[1]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeTrue()
            ->and(count($gameEvents->findAllOfType(InsuranceForPlayerWasConcluded::class)))->toBe(2);

        $this->coreGameLogic->handle($this->gameId,
            CancelInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeTrue()
            ->and(count($gameEvents->findAllOfType(InsuranceForPlayerWasCancelled::class)))->toBe(1);

        $this->coreGameLogic->handle($this->gameId,
            CancelInsuranceForPlayer::create($this->players[0], $this->insurances[1]->id));
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
            $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0],
                $this->insurances[1]->id))->toBeFalse()
            ->and(count($gameEvents->findAllOfType(InsuranceForPlayerWasConcluded::class)))->toBe(3);
    });
});

describe('getCostOfAllInsurances', function () {
    it('returns correct sum of all insurance costs', function () {
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getCostOfAllInsurances($gameEvents, $this->players[0])->value)->toEqual(100);

        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[1]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getCostOfAllInsurances($gameEvents, $this->players[0])->value)->toEqual(250);

        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[2]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getCostOfAllInsurances($gameEvents, $this->players[0])->value)->toEqual(750);
    });
});

describe('getLoansForPlayer', function () {
    it('returns empty array if no loans exist', function () {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(MoneySheetState::getLoansForPlayer($gameEvents, $this->players[0]))->toBeEmpty();
    });

    it('returns existing loans', function () {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents,
            $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE);

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625;
        $takeoutLoanForm->guthaben = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $loanId = LoanId::unique();
        $takeoutLoanForm->loanId = $loanId->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $loans = MoneySheetState::getLoansForPlayer($gameEvents, $this->players[0]);

        expect($loans)->toHaveCount(1)
            ->and($loans[0]->year->value)->toEqual(1)
            ->and($loans[0]->loanId)->toEqual($loanId)
            ->and($loans[0]->loanData->loanAmount->value)->toEqual(10000)
            ->and($loans[0]->loanData->totalRepayment->value)->toEqual(12500)
            ->and($loans[0]->loanData->repaymentPerKonjunkturphase->value)->toEqual(625)
            ->and(MoneySheetState::getSumOfAllLoansForPlayer($gameEvents, $this->players[0])->value)->toEqual(10000)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE + 10000);

    });
});

describe('getOpenRatesForLoan', function () {
    it('throws an exception if no loans exist', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], new LoanId('test'))->value);
    })->throws(\RuntimeException::class, 'No loan found for player p1 with ID test');

    it('returns correct open rates for loans', function () {
        /** @var TestCase $this */
        $cardsForTesting = [];
        for ($i = 0; $i < count($this->players); $i++) {
            $cardID = new CardId('cardToRemoveZeitsteine' . $i);
            $cardsForTesting[] = new KategorieCardDefinition(
                id: $cardID,
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                // add the money per round the player loses
                    guthabenChange: new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE),
                    zeitsteineChange: -1 * ($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(count($this->players)) - 1)
                ),
            );
        }
        $testCards = [
            ...$cardsForTesting,
            ...$this->getCardsForSozialesAndFreizeit(),
            ...$this->getCardsForJobs(),
            ...$this->getCardsForMinijobs(),
            ...$this->getCardsForBildungAndKarriere(),
            ...$this->getCardsForEreignisse(),
        ];
        CardFinder::getInstance()->overrideCardsForTesting($testCards);

        // make sure we always use the same konjunkturphase definition for each round
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition
        ]);

        // start new konjunkturphase to use the new cards
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting()
        );

        $initialGuthaben = Configuration::STARTKAPITAL_VALUE;
        $loanAmount = 10000;
        $repayment = 12500;
        $rate = 625;

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = $loanAmount;
        $takeoutLoanForm->totalRepayment = $repayment;
        $takeoutLoanForm->repaymentPerKonjunkturphase = $rate;
        $takeoutLoanForm->guthaben = $initialGuthaben;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedYear = 2;
        $loans = MoneySheetState::getLoansForPlayer($gameEvents, $this->players[0]);
        $openRates = MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId);
        $year = KonjunkturphaseState::getCurrentYear($gameEvents);
        expect($loans)->toHaveCount(1)
            ->and($loans[0]->year->value)->toEqual($expectedYear)
            ->and($openRates->value)->toEqual($repayment)
            ->and($year->value)->toEqual($expectedYear)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0])->value)->toEqual($initialGuthaben + $loanAmount);

        $expectedGuthaben = $initialGuthaben + $loanAmount;
        for ($i = 1; $i <= 20; $i++) {
            $expectedYear++;
            $expectedOpenRates = $repayment - ($rate * $i);
            $expectedGuthaben -= $rate;
            $this->makeSpielzugForPlayersAndChangeKonjunkturphase();

            $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
            $openRates = MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId);
            $year = KonjunkturphaseState::getCurrentYear($gameEvents);
            expect($loans)->toHaveCount(1)
                ->and($openRates->value)->toEqual($expectedOpenRates)
                ->and($year->value)->toEqual($expectedYear)
                ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                    $this->players[0])->value)->toEqual($expectedGuthaben);
        }

        // after 20 years, the loan should be fully repaid
        expect(PlayerState::getGuthabenForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($initialGuthaben + $loanAmount - $repayment);
        $openRates = MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId);
        expect($openRates->value)->toEqual(0);

        // the loan rates should not be paid anymore the next year
        $this->makeSpielzugForPlayersAndChangeKonjunkturphase();
        expect(PlayerState::getGuthabenForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($initialGuthaben + $loanAmount - $repayment);
        $openRates = MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId);
        expect($openRates->value)->toEqual(0);
    })->todo('fix this...good thing it\s only to million lines...');
});

describe("getAnnualExpensesForPlayer", function () {
    it('returns the lebenserhaltungskosten if player has no other expenses', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(MoneySheetState::getAnnualExpensesForPlayer($gameEvents,
            $this->players[0])->value)->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });

    it('returns annual expenses', function () {
        /** @var TestCase $this */
        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625;
        $takeoutLoanForm->guthaben = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedAnnualExpenses = Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE + 625; // 5000 Lebenshaltungskosten + 625 loan repayment
        expect(MoneySheetState::getAnnualExpensesForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($expectedAnnualExpenses);

        // player 0 takes out a second loan
        $takeoutLoanForm->loanAmount = 1000;
        $takeoutLoanForm->totalRepayment = 1250;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 62.5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedAnnualExpenses += 62.5; // add second loan repayment
        expect(MoneySheetState::getAnnualExpensesForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($expectedAnnualExpenses);

        // player 0 concludes an insurance
        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedAnnualExpenses += 100; // add insurance cost
        expect(MoneySheetState::getAnnualExpensesForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($expectedAnnualExpenses);

        // player 0 takes a job
        $cardsForTesting = [
            new JobCardDefinition(
                id: new CardId('tj0'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(10000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId("tj0")));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedAnnualExpenses += 2500; // add job taxes (25% of 10000)
        expect(MoneySheetState::getAnnualExpensesForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($expectedAnnualExpenses);
    });

});

describe("getAnnualIncomeForPlayer", function () {
    it('returns no income', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(MoneySheetState::getAnnualIncomeForPlayer($gameEvents, $this->players[0])->value)->toEqual(0);
    });

    it('returns gehalt if player has a job', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
                new JobCardDefinition(
                    id: new CardId('j0'),
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(10000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId("j0")));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getAnnualIncomeForPlayer($gameEvents, $this->players[0])->value)->toEqual(10000);
    });

    it('returns dividend for stocks bought', function () {
        /** @var TestCase $this */
        $amountOfStocks = 100;

        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            BuyStocksForPlayer::create(
                $this->players[0],
                StockType::LOW_RISK,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedDividend = $this->konjunkturphaseDefinition->getAuswirkungByScope(AuswirkungScopeEnum::DIVIDEND)->modifier;
        expect(MoneySheetState::getAnnualIncomeForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($expectedDividend * $amountOfStocks);
    });
});
