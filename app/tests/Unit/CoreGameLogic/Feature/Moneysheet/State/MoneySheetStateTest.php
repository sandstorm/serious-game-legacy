<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet\State;

use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\Dto\Zeitslots;
use Domain\Definitions\Konjunkturphase\Dto\ZeitslotsPerPlayer;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
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
            ->and($actual->fine)->toEqual(new MoneyAmount(Configuration::FINE_VALUE));
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
            ->and($actual->fine)->toEqual(new MoneyAmount(Configuration::FINE_VALUE));
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

        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[1]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        // Player already payed for insurance this Konjunkturphase (when they took out the insurance)
        expect(MoneySheetState::getCostOfAllInsurances($gameEvents, $this->players[0])->value)->toEqual(0);

        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());

        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[2]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        // Player must pay for the first two insurances
        expect(MoneySheetState::getCostOfAllInsurances($gameEvents, $this->players[0])->value)->toEqual(250);
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
        $takeoutLoanForm->sumOfAllAssets = Configuration::STARTKAPITAL_VALUE;
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
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE + 10000);

    });
});

describe('getOpenRatesForLoan', function () {
    it('throws an exception if no loans exist', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], new LoanId('test')));
    })->throws(\RuntimeException::class, 'No loan found for player p1 with ID test');

    it('returns correct open rates for loans', function () {

        $testPhase = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(1),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Test',
            description: '',
            additionalEvents: '',
            zeitsteine: new Zeitsteine(
                [
                    new ZeitsteinePerPlayer(2, 1),
                ]
            ),
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 4),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 4),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                    ])
                ),
            ],
            modifierIds: [],
            modifierParameters: new ModifierParameters(),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.40
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 0
                ),
            ]
        );

        // make sure we always use the same konjunkturphase definition for each round
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $testPhase
        ]);

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
        $takeoutLoanForm->sumOfAllAssets = $initialGuthaben;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $expectedYear = 1;
        $expectedGuthaben = $initialGuthaben + $loanAmount;
        $loans = MoneySheetState::getLoansForPlayer($gameEvents, $this->players[0]);
        $openRates = MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId);
        $year = KonjunkturphaseState::getCurrentYear($gameEvents);
        expect($loans)->toHaveCount(1)
            ->and($loans[0]->year->value)->toEqual($expectedYear)
            ->and($loans[0]->loanData->repaymentPerKonjunkturphase->value)->toEqual($rate)
            ->and($openRates)->toEqual(Configuration::REPAYMENT_PERIOD)
            ->and($year->value)->toEqual($expectedYear)
            ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                $this->players[0])->value)->toEqual($expectedGuthaben);

        for ($i = 1; $i <= 20; $i++) {
            $expectedYear++;
            $expectedGuthaben -= $rate;
            $expectedGuthaben -= Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE;
            $this->makeSpielzugForPlayersByDoingAMiniJob();

            $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
            /** @var null|MinijobWasDone $miniJob */
            $miniJob = $gameEvents->findLastOrNullWhere(
                fn($e) => $e instanceof MinijobWasDone && $e->playerId->equals($this->players[0])
            );
            $expectedGuthaben += $miniJob->getResourceChanges($this->players[0])->guthabenChange->value; // mini job income

            $openRates = MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId);
            expect($loans)->toHaveCount(1)
                ->and($openRates)->toEqual(Configuration::REPAYMENT_PERIOD - $i)
                ->and(KonjunkturphaseState::getCurrentYear($gameEvents)->value)->toEqual($expectedYear)
                ->and(PlayerState::getGuthabenForPlayer($gameEvents,
                    $this->players[0])->value)->toEqual($expectedGuthaben);
        }

        // after 20 years, the loan should be fully repaid
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0])->value)->toEqual($expectedGuthaben)
            ->and(MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId))->toEqual(0);

        // the loan rates should not be paid anymore the next year
        $this->makeSpielzugForPlayersByDoingAMiniJob();
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        /** @var null|MinijobWasDone $miniJob */
        $miniJob = $gameEvents->findLastOrNullWhere(
            fn($e) => $e instanceof MinijobWasDone && $e->playerId->equals($this->players[0])
        );
        $expectedGuthaben += $miniJob->getResourceChanges($this->players[0])->guthabenChange->value; // mini job income
        $expectedGuthaben -= Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE;
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0])->value)->toEqual($expectedGuthaben)
            ->and(MoneySheetState::getOpenRatesForLoan($gameEvents, $this->players[0], $loans[0]->loanId))->toEqual(0);
    });
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
        $takeoutLoanForm->sumOfAllAssets = Configuration::STARTKAPITAL_VALUE;
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

        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId("tj0")));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedAnnualExpenses += 2500; // add job taxes (25% of 10000)
        $expectedAnnualExpenses += 100; // add insurance cost (was taken out last Konjunkturphase)
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
            BuyInvestmentsForPlayer::create(
                $this->players[0],
                InvestmentId::MERFEDES_PENZ,
                $amountOfStocks
            )
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedDividend = $this->konjunkturphaseDefinition->getAuswirkungByScope(AuswirkungScopeEnum::DIVIDEND)->value;
        expect(MoneySheetState::getAnnualIncomeForPlayer($gameEvents,
            $this->players[0])->value)->toEqual($expectedDividend * $amountOfStocks);
    });
});
