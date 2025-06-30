<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Tests\TestCase;

describe('handleChangeKonjunkturphase', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('redistributes Zeitsteine', function () {
        // Make sure the initial number of Zeitsteine is what we expect
        $expectedNumberOfZeitsteine = 6;
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);

        // use a Zeitstein
        $cardToActivate = array_shift($this->cardsBildung);
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine-1);

        // Change Konjunkturphase
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedKonjunkturphaseForTesting(new KonjunkturphaseDefinition(
                id: KonjunkturphasenId::create(161),
                type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
                description: 'no changes',
                additionalEvents: '',
                zinssatz: 5,
                kompetenzbereiche: [],
                auswirkungen: []
            )));

        // Expect the number of Zeitsteine to be the initial value again
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);
    });

    it('type of the next konjunkturphase after the first one has correct type', function() {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        expect($lastPhase)->not()->toBeNull();
        expect($lastPhase->type)->toBe(KonjunkturphaseTypeEnum::AUFSCHWUNG);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        expect($lastPhase)->not()->toBeNull();
        expect($lastPhase->type)->toBeIn([
            KonjunkturphaseTypeEnum::AUFSCHWUNG,
            KonjunkturphaseTypeEnum::BOOM,
            KonjunkturphaseTypeEnum::REZESSION
        ]);
    });

    it('happens automatically when no player has any Zeitsteine remaining', function () {
        // TODO implement
    })->todo('not yet implemented');
});

describe('getListOfPossibleNextPhaseTypes', function () {
    it('start of game -> first phase type is Aufschwung', function () {
        /** @var TestCase $this */
        $this->setupBasicGameWithoutKonjunkturphase();

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = $gameEvents->findLastOrNull(KonjunkturphaseWasChanged::class);
        expect($lastPhase)->toBeNull();

        $possibleNextPhases = KonjunkturphaseFinder::getListOfPossibleNextPhaseTypes();
        expect($possibleNextPhases)->toBeArray()
            ->and($possibleNextPhases)->toHaveCount(1)
            ->and($possibleNextPhases[0])->tobe(KonjunkturphaseTypeEnum::AUFSCHWUNG);
    });

    it('game started -> last phase was of type Aufschwung', function () {
        /** @var TestCase $this */
        $this->setupBasicGame();

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        expect($lastPhase)->not()->toBeNull();
        expect($lastPhase->type)->toBe(KonjunkturphaseTypeEnum::AUFSCHWUNG);

        $possibleNextPhases = KonjunkturphaseFinder::getListOfPossibleNextPhaseTypes($lastPhase->type);
        expect($possibleNextPhases)->toBeArray()
            ->and($possibleNextPhases)->toHaveCount(3)
            ->and($possibleNextPhases[0])->tobe(KonjunkturphaseTypeEnum::AUFSCHWUNG)
            ->and($possibleNextPhases[1])->tobe(KonjunkturphaseTypeEnum::BOOM)
            ->and($possibleNextPhases[2])->tobe(KonjunkturphaseTypeEnum::REZESSION);
    });
});
