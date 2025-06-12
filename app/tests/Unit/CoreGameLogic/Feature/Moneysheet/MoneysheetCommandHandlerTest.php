<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet;

use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;
use Domain\Definitions\Card\ValueObject\PileId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleEnterSteuernUndAbgabenForPlayer', function () {
    it('works for correct player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], 0));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(0)
            ->and($actualEvent->getExpectedInput())->toEqual(0)
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for correct player input when the player has a job', function () {
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $playerInput = 8500;
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($playerInput)
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for incorrect player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], 200));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(200)
            ->and($actualEvent->getExpectedInput())->toEqual(0)
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('works for incorrect player input when the player has a job', function () {
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $playerInput = 7500;
        $expectedValue = 8500;
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var SteuernUndAbgabenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(SteuernUndAbgabenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($expectedValue)
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('charges a fee after two incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(50000);

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], 200));
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], 300));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(49750);
    });

    it('charges no fee after one incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(50000);

        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->players[0], 200));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(50000);
    });
});

describe('handleEnterLebenshaltungskostenForPlayer', function () {
    it('works for correct player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], 5000));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(5000)
            ->and($actualEvent->getExpectedInput())->toEqual(5000)
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for correct player input when the player has a job', function () {
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $playerInput = 11900;
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($playerInput)
            ->and($actualEvent->wasInputCorrect())->toBeTrue();
    });

    it('works for incorrect player input when the player has no job', function () {
        /** @var TestCase $this */

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], 200));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual(200)
            ->and($actualEvent->getExpectedInput())->toEqual(5000)
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('works for incorrect player input when the player has a job', function () {
        /** @var TestCase $this */

        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'offered 1',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new Gehalt(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                    ),
                ),
            ]
        ]);
        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('j0')));

        $playerInput = 7500;
        $expectedValue = 11900;
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], $playerInput));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        /** @var LebenshaltungskostenForPlayerWereEntered $actualEvent */
        $actualEvent = $gameEvents->findLast(LebenshaltungskostenForPlayerWereEntered::class);
        expect($actualEvent->getPlayerInput())->toEqual($playerInput, 'Player input should be ' . $playerInput)
            ->and($actualEvent->getExpectedInput())->toEqual($expectedValue)
            ->and($actualEvent->wasInputCorrect())->toBeFalse();
    });

    it('charges a fee after two incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(50000);

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], 200));
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], 300));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(49750);
    });

    it('charges no fee after one incorrect entries', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(50000);

        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->players[0], 200));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]))->toEqual(50000);
    });
});
