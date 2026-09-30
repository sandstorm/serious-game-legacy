<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\State\EreignisPrerequisiteChecker;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Tests\TestCase;

@covers(EreignisPrerequisiteChecker::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('hasPlayerPrerequisites', function () {
    it('checks HAS_JOB and HAS_NO_JOB for a player without a job', function () {
        /** @var TestCase $this */
        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());

        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_JOB))->toBeFalse()
            ->and($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_NO_JOB))->toBeTrue();
    });

    it('checks HAS_JOB and HAS_NO_JOB for a player with a job', function () {
        /** @var TestCase $this */
        $this->startNewKonjunkturphaseWithCardsOnTop([
            new JobCardDefinition(
                id: new CardId('job1234'),
                title: 'for testing',
                description: 'easy job',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(20000),
                requirements: new JobRequirements(),
            ),
        ]);
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(AcceptJobOffer::create($this->players[0], new CardId('job1234')));

        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());

        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_JOB))->toBeTrue()
            ->and($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_NO_JOB))->toBeFalse()
            // the other player is not affected
            ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_NO_JOB))->toBeTrue();
    });
});
