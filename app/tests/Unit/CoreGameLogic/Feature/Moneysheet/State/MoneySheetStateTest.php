<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\PileId;
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

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(14000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualKosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $this->players[0]);
        expect($actualKosten)->toEqual(new MoneyAmount(5000));
    });

    it('returns 35% of the Gehalt it that is more than 5000', function () {
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

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

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

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

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameEvents, $this->players[0]))->toBe(1);
    });

    it('resets tries when an event happens which changes the calculation', function () {
        /** @var TestCase $this */
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(200)));

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

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeTrue()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong once', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeFalse()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong twice', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3000)));
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3080)));

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

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(5000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastLebenshaltungskostenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeTrue()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong once', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getResultOfLastLebenshaltungskostenInput($gameEvents, $this->players[0]);
        expect($actual->wasSuccessful)->toBeFalse()
            ->and($actual->fine)->toEqual(new MoneyAmount(0));
    });

    it('works if input was wrong twice', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3000)));
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3080)));

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
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(0)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(0));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(8500)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(8500));
    });

    it('returns correct input after one incorrect player input', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(2000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForSteuernUndAbgaben($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(2000));
    });

    it('returns correct input after two incorrect player inputs', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(2000)));
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], new MoneyAmount(3000)));

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
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(5000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(5000));

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(11900)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(11900));
    });

    it('returns correct input after one incorrect player input', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(2000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(2000));
    });

    it('returns correct input after two incorrect player inputs', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(2000)));
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(3000)));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actual = MoneySheetState::getLastInputForLebenshaltungskosten($gameEvents, $this->players[0]);
        expect($actual)->toEqual(new MoneyAmount(5000)); // expect corrected value
    });
});
