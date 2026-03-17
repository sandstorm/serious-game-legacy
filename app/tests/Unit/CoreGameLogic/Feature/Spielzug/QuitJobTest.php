<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleQuitJob', function () {
    it('successfully quits a job and releases bound Zeitstein', function () {
        /** @var TestCase $this */
        $testJobs = [
            new JobCardDefinition(
                id: new CardId('testJob'),
                title: 'Test Job',
                description: 'A test job.',
                gehalt: new MoneyAmount(30000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($testJobs);

        $gameEvents = $this->getGameEvents();
        $initialZeitsteine = PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]);

        // accept job (costs 1 Zeitstein + binds 1 Zeitstein permanently)
        $this->handle(AcceptJobOffer::create($this->players[0], new CardId('testJob')));

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->players[0]))->not->toBeNull()
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toBe($initialZeitsteine - 2);

        // end turn for player 0
        $this->handle(new EndSpielzug($this->players[0]));

        // player 1 does mini job and ends turn
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // player 0 quits job in next turn
        $this->handle(QuitJob::create($this->players[0]));

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->players[0]))->toBeNull()
            // bound Zeitstein is released: player gets back 1 Zeitstein compared to before quit
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))
            ->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('allows player to accept a new job after quitting', function () {
        /** @var TestCase $this */
        $testCards = [
            new JobCardDefinition(
                id: new CardId('testJob1'),
                title: 'First Job',
                description: 'First test job.',
                gehalt: new MoneyAmount(30000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            new JobCardDefinition(
                id: new CardId('testJob2'),
                title: 'Second Job',
                description: 'Second test job.',
                gehalt: new MoneyAmount(50000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
            new MinijobCardDefinition(
                id: new CardId('extraMj'),
                title: 'Extra Minijob',
                description: 'Extra minijob for testing.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($testCards);

        // player 0 accepts first job
        $this->handle(AcceptJobOffer::create($this->players[0], new CardId('testJob1')));
        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->players[0])?->getId())->toEqual(new CardId('testJob1'));

        // end turn for player 0
        $this->handle(new EndSpielzug($this->players[0]));

        // player 1 does mini job and ends turn
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // player 0 quits first job and does a minijob to be able to end turn
        $this->handle(QuitJob::create($this->players[0]));
        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->players[0]))->toBeNull();

        $this->handle(DoMinijob::create($this->players[0]));
        $this->handle(new EndSpielzug($this->players[0]));

        // player 1 does mini job and ends turn
        $this->handle(DoMinijob::create($this->players[1]));
        $this->handle(new EndSpielzug($this->players[1]));

        // start new Konjunkturphase so testJob2 is available on the jobs pile
        $this->startNewKonjunkturphaseWithCardsOnTop($testCards);

        // player 0 accepts second job
        $this->handle(AcceptJobOffer::create($this->players[0], new CardId('testJob2')));
        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->players[0])?->getId())->toEqual(new CardId('testJob2'));
    });
});
