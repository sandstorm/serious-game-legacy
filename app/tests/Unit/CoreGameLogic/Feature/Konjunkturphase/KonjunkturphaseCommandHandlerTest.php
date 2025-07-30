<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Tests\TestCase;

describe('handleChangeKonjunkturphase', function () {
    beforeEach(function () {
        $cardsForTesting = [
            "cardForTesting" => new KategorieCardDefinition(
                id: new CardId('cardForTesting'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                year: new Year(1),
                resourceChanges: new ResourceChanges()
            ),
        ];

        /** @var TestCase $this */
        $this->setupBasicGame(cards: $cardsForTesting);
    });

    it('redistributes Zeitsteine', function () {
        /** @var TestCase $this */
        // Make sure the initial number of Zeitsteine is what we expect
        $expectedNumberOfZeitsteine = $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2);
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);

        // use a Zeitstein

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine - 1);

        // Change Konjunkturphase
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        // Expect the number of Zeitsteine to be the initial value again
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);
    });

    it('type of the next konjunkturphase after the first one has correct type', function() {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        expect($lastPhase)->not()->toBeNull()
            ->and($lastPhase->type)->toBe(KonjunkturphaseTypeEnum::AUFSCHWUNG)
            ->and(KonjunkturphaseState::getCurrentYear($gameEvents)->value)->toBe(1);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        expect($lastPhase)->not()->toBeNull()
            ->and($lastPhase->type)->toBeIn([
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
                KonjunkturphaseTypeEnum::BOOM,
                KonjunkturphaseTypeEnum::REZESSION
            ])
            ->and(KonjunkturphaseState::getCurrentYear($gameEvents)->value)->toBe(2);
    });

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
