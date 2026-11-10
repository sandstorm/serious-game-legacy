<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Tests\DomainTestCase;

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

        /** @var DomainTestCase $this */
        $this->setupBasicGame(cards: $cardsForTesting);
    });

    it('redistributes Zeitsteine', function () {
        /** @var DomainTestCase $this */
        // Make sure the initial number of Zeitsteine is what we expect
        $expectedNumberOfZeitsteine = $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2);
        /** @var GameEvents $stream */
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);

        // use a Zeitstein

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe($expectedNumberOfZeitsteine);

        $this->handle(new StartSpielzug($this->players[0]));
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

    it('type of the next konjunkturphase after the first one has correct type', function () {
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
        /** @var DomainTestCase $this */
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
        /** @var DomainTestCase $this */
        $this->setupBasicGame();

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        expect($lastPhase)->not()->toBeNull()
            ->and($lastPhase->type)->toBe(KonjunkturphaseTypeEnum::AUFSCHWUNG);

        $possibleNextPhases = KonjunkturphaseFinder::getListOfPossibleNextPhaseTypes($lastPhase->type);
        expect($possibleNextPhases)->toBeArray()
            ->and($possibleNextPhases)->toHaveCount(3)
            ->and($possibleNextPhases[0])->tobe(KonjunkturphaseTypeEnum::AUFSCHWUNG)
            ->and($possibleNextPhases[1])->tobe(KonjunkturphaseTypeEnum::BOOM)
            ->and($possibleNextPhases[2])->tobe(KonjunkturphaseTypeEnum::REZESSION);
    });

    it('returns Rezession with increased likelihood when isChanceForRezessionIncreased is true', function () {
        /** @var DomainTestCase $this */
        $this->setupBasicGame();

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $lastPhase = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);

        for ($i = 0; $i < 100; $i++) {
            $possibleNextPhases = KonjunkturphaseFinder::getListOfPossibleNextPhaseTypes(
                konjunkturphaseType: $lastPhase->type,
                isChanceForRezessionIncreased: true
            );
            echo implode(", ", array_map(fn ($phase) => $phase->value, $possibleNextPhases)) . "\n\n";
        }
    })->skip("Un-skip this to debug increased chance for Rezession");
});

describe('increased chance for a Rezession', function () {
    beforeEach(function () {
        /** @var DomainTestCase $this */
        $this->setupBasicGame();
    });

    it('is active during the Konjunkturphase with the modifier and not in the next one', function () {
        /** @var DomainTestCase $this */
        $normalKonjunkturphase = $this->getKonjunkturphaseDefinition();
        $konjunkturphaseWithIncreasedChance = $this->konjunkturphaseDefinitionWithModifiers(
            KonjunkturphasenId::create(2),
            [...$normalKonjunkturphase->modifierIds, ModifierId::INCREASED_CHANCE_FOR_REZESSION],
        );
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([$normalKonjunkturphase, $konjunkturphaseWithIncreasedChance]);
        // this is what the KonjunkturphaseCommandHandler uses to pick the next Konjunkturphase
        $isChanceForRezessionIncreased = fn () => ModifierCalculator::forStream($this->getGameEvents())
            ->withoutPlayer()
            ->modify($this->getGameEvents(), HookEnum::INCREASED_CHANCE_FOR_REZESSION, false);

        expect($isChanceForRezessionIncreased())->toBeFalse();

        $this->setKonjunkturphaseDefinition($konjunkturphaseWithIncreasedChance);
        $this->startNewKonjunkturphaseWithCardsOnTop([]);
        expect($isChanceForRezessionIncreased())->toBeTrue();

        $this->setKonjunkturphaseDefinition($normalKonjunkturphase);
        $this->startNewKonjunkturphaseWithCardsOnTop([]);
        expect($isChanceForRezessionIncreased())->toBeFalse();
    });
});

describe('investment prices at a Konjunkturphase change', function () {
    beforeEach(function () {
        /** @var DomainTestCase $this */
        $this->setupBasicGame();

        KonjunkturphaseFinder::initializeForTesting();
        $this->boomIV = KonjunkturphaseFinder::findKonjunkturphaseById(KonjunkturphasenId::create(8)); // Aktien Kursbonus +12
        $this->depressionII = KonjunkturphaseFinder::findKonjunkturphaseById(KonjunkturphasenId::create(14)); // Aktien Kursbonus -26
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->getKonjunkturphaseDefinition(),
            $this->boomIV,
            $this->depressionII,
        ]);

        $this->changeKonjunkturphaseTo = fn (KonjunkturphaseDefinition $konjunkturphase) => $this->handle(
            ChangeKonjunkturphase::create()
                ->withFixedKonjunkturphaseForTesting($konjunkturphase)
                ->withFixedCardOrderForTesting()
        );
        $this->getMerfedesPenzPrice = fn () => InvestmentPriceState::getCurrentInvestmentPrice(
            $this->getGameEvents(),
            InvestmentId::MERFEDES_PENZ
        )->value;
    });

    // Merfedes-Penz has no jumps, so its exponent is 0.07 - 0.5 * 0.15^2 ± 0.15 + Kursbonus.
    // With Boom IV (+0.12) the price always rises, with Depression II (-0.26) it always falls.
    it('uses the Kursbonus of the new Konjunkturphase when changing from Boom to Depression', function () {
        ($this->changeKonjunkturphaseTo)($this->boomIV);
        $priceBefore = ($this->getMerfedesPenzPrice)();

        ($this->changeKonjunkturphaseTo)($this->depressionII);

        expect(($this->getMerfedesPenzPrice)())->toBeLessThan($priceBefore);
    });

    it('uses the Kursbonus of the new Konjunkturphase when changing from Depression to Boom', function () {
        ($this->changeKonjunkturphaseTo)($this->depressionII);
        $priceBefore = ($this->getMerfedesPenzPrice)();

        ($this->changeKonjunkturphaseTo)($this->boomIV);

        expect(($this->getMerfedesPenzPrice)())->toBeGreaterThan($priceBefore);
    });
});
