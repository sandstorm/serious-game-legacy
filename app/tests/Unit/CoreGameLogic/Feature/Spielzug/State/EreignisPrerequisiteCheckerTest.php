<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\State\EreignisPrerequisiteChecker;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Tests\DomainTestCase;

beforeEach(function () {
    /** @var DomainTestCase $this */
    $this->setupBasicGame();
});

/**
 * Lets player 0 activate a Bildung & Karriere card in a Konjunkturphase in which every activated card triggers an
 * Ereignis, so the given Ereignis card is triggered for player 0.
 */
function triggerEreignisForFirstPlayer(DomainTestCase $testCase, EreignisCardDefinition $ereignisCard): void
{
    $konjunkturphaseThatAlwaysTriggersAnEreignis = $testCase->konjunkturphaseDefinitionWithModifiers(
        $testCase->getKonjunkturphaseDefinition()->id,
        [ModifierId::FOR_TESTING_ONLY_ALWAYS_TRIGGER_EREIGNIS],
    );
    KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([$konjunkturphaseThatAlwaysTriggersAnEreignis]);
    $testCase->setKonjunkturphaseDefinition($konjunkturphaseThatAlwaysTriggersAnEreignis);

    $testCase->startNewKonjunkturphaseWithCardsOnTop([
        $ereignisCard,
        new KategorieCardDefinition(
            id: new CardId('cardToTriggerEreignis'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: 'for testing',
            resourceChanges: new ResourceChanges(),
        ),
    ]);
    $testCase->handle(new StartSpielzug($testCase->getPlayers()[0]));
    $testCase->handle(ActivateCard::create($testCase->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));

    expect($testCase->getGameEvents()->findLast(EreignisWasTriggered::class)->ereignisCardId)->toEqual($ereignisCard->getId());
}

/**
 * @param ModifierId[] $modifierIds
 */
function ereignisCardForPrerequisiteTest(string $id, array $modifierIds = [], ModifierParameters $modifierParameters = new ModifierParameters()): EreignisCardDefinition
{
    return new EreignisCardDefinition(
        id: new CardId($id),
        categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
        title: 'for testing',
        description: 'for testing',
        phaseId: LebenszielPhaseId::PHASE_1,
        year: new Year(1),
        resourceChanges: new ResourceChanges(),
        modifierIds: $modifierIds,
        modifierParameters: $modifierParameters,
        ereignisRequirementIds: [],
        gewichtung: 1,
    );
}

describe('hasPlayerPrerequisites', function () {
    it('checks HAS_JOB and HAS_NO_JOB for a player without a job', function () {
        /** @var DomainTestCase $this */
        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());

        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_JOB))->toBeFalse()
            ->and($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_NO_JOB))->toBeTrue();
    });

    it('checks HAS_JOB and HAS_NO_JOB for a player with a job', function () {
        /** @var DomainTestCase $this */
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

    it('checks HAS_CHILD and HAS_NO_CHILD', function () {
        /** @var DomainTestCase $this */
        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_CHILD))->toBeFalse()
            ->and($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_NO_CHILD))->toBeTrue();

        triggerEreignisForFirstPlayer($this, ereignisCardForPrerequisiteTest(
            'childbirth',
            [ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE],
            new ModifierParameters(modifyAdditionalLebenshaltungskostenPercentage: 10),
        ));

        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_CHILD))->toBeTrue()
            ->and($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_NO_CHILD))->toBeFalse()
            // the other player is not affected
            ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_CHILD))->toBeFalse()
            ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_NO_CHILD))->toBeTrue();
    });

    it('checks HAS_LOAN', function () {
        /** @var DomainTestCase $this */
        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_LOAN))->toBeFalse();

        $this->handle(TakeOutALoanForPlayer::create($this->players[0], 10000));

        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_LOAN))->toBeTrue()
            // the other player is not affected
            ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_LOAN))->toBeFalse();
    });

    it('is always fulfilled for NO_PREREQUISITES', function () {
        /** @var DomainTestCase $this */
        $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());

        expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::NO_PREREQUISITES))->toBeTrue();
    });

    describe('HAS_SPECIFIC_CARD', function () {
        it('is not fulfilled if the player has not played the card', function () {
            /** @var DomainTestCase $this */
            $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());

            expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('buk0')))->toBeFalse();
        });

        it('is fulfilled if the player has activated the card', function () {
            /** @var DomainTestCase $this */
            $this->startNewKonjunkturphaseWithCardsOnTop([
                new KategorieCardDefinition(
                    id: new CardId('requiredCard'),
                    categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                    title: 'for testing',
                    description: 'for testing',
                    resourceChanges: new ResourceChanges(),
                ),
            ]);
            $this->handle(new StartSpielzug($this->players[0]));
            $this->handle(ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

            $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
            expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('requiredCard')))->toBeTrue()
                ->and($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('buk0')))->toBeFalse()
                // the other player is not affected
                ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('requiredCard')))->toBeFalse();
        });

        it('is fulfilled if the player has accepted the job', function () {
            /** @var DomainTestCase $this */
            $this->startNewKonjunkturphaseWithCardsOnTop([
                new JobCardDefinition(
                    id: new CardId('requiredJob'),
                    title: 'for testing',
                    description: 'easy job',
                    phaseId: LebenszielPhaseId::PHASE_1,
                    year: new Year(1),
                    gehalt: new MoneyAmount(20000),
                    requirements: new JobRequirements(),
                ),
            ]);
            $this->handle(new StartSpielzug($this->players[0]));
            $this->handle(AcceptJobOffer::create($this->players[0], new CardId('requiredJob')));

            $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
            expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('requiredJob')))->toBeTrue()
                // the other player is not affected
                ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('requiredJob')))->toBeFalse();
        });

        it('is fulfilled if the Ereignis was triggered for the player', function () {
            /** @var DomainTestCase $this */
            triggerEreignisForFirstPlayer($this, ereignisCardForPrerequisiteTest('requiredEreignis'));

            $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());
            expect($checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('requiredEreignis')))->toBeTrue()
                // the other player is not affected
                ->and($checker->hasPlayerPrerequisites($this->players[1], EreignisPrerequisitesId::HAS_SPECIFIC_CARD, new CardId('requiredEreignis')))->toBeFalse();
        });

        it('throws if no required card is given', function () {
            /** @var DomainTestCase $this */
            $checker = EreignisPrerequisiteChecker::forStream($this->getGameEvents());

            $checker->hasPlayerPrerequisites($this->players[0], EreignisPrerequisitesId::HAS_SPECIFIC_CARD);
        })->throws(\RuntimeException::class, 'Required Card Id not specified', 1755769328);
    });
});
