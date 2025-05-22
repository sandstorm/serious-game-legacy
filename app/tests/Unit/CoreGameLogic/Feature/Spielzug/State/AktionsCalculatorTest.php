<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\Definitions\Cards\Model\CardDefinition;
use Domain\Definitions\Pile\Enum\PileEnum;
use Domain\Definitions\Pile\PileFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
});

test('welche Spielzüge hat player zur Verfügung', function () {
    $p1 = PlayerId::fromString('p1');
    $p2 = PlayerId::fromString('p2');

    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $p1,
            $p2
        ]
    ));

    $pileIdBildung = new PileId(PileEnum::BILDUNG_PHASE_1);
    $cardsBildung = PileFinder::getCardsIdsForPile($pileIdBildung);
    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $pileIdBildung, cards: $cardsBildung),
        ));

    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1')
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1)[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC

    $this->coreGameLogic->handle($this->gameId, new SkipCard($p1, array_shift($cardsBildung), $pileIdBildung));
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($p1, array_shift($cardsBildung), $pileIdBildung)
            ->withEreignis(new EreignisId("EVENT:OmaKrank")));
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($p1));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($p1))[0]->id->value)->toBe("MODIFIER:ausetzen")
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1))->toBeEmpty()
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p2)[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC
});

describe('canPlayerActivateCard', function () {
    beforeEach(function () {
        // setup player
        $this->playerId1 = PlayerId::fromString('p1');
        $this->playerId2 = PlayerId::fromString('p2');
        $this->stream = GameEvents::fromArray([
            new PreGameStarted(
                playerIds: [$this->playerId1, $this->playerId2],
                resourceChanges: new ResourceChanges(
                    guthabenChange: 50000,
                    zeitsteineChange: 3
                ),
            ),
            new GameWasStarted(
                playerOrdering: [
                    $this->playerId1,
                    $this->playerId2,
                ]
            ),
        ]);
    });

    it('returns true when player can activate the card', function () {
        $pileId = new PileId(PileEnum::BILDUNG_PHASE_1);
        $cardId = new CardId('testcard');
        $cardToTest = new CardDefinition(
            id: $cardId,
            pileId: $pileId,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 200,
                zeitsteine: 1
            ),
        );
        $cardToTest2 = new CardDefinition(
            id: $cardId,
            pileId: $pileId,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 50000,
                zeitsteine: 3
            ),
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $pileId, cards: [$cardId]),
            ));

        $stream = $this->stream;
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerActivateCard($this->playerId1, $cardId, $cardToTest))->toBeTrue()
            ->and($actionsCalculatorUnderTest->canPlayerActivateCard($this->playerId1, $cardId, $cardToTest2))->toBeTrue();
    });

    it('returns false when player cannot activate the card', function () {
        $pileId = new PileId(PileEnum::BILDUNG_PHASE_1);
        $cardId = new CardId('testcard');
        $cardToTest1 = new CardDefinition(
            id: $cardId,
            pileId: $pileId,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 50001,
            ),
        );
        $cardToTest2 = new CardDefinition(
            id: $cardId,
            pileId: $pileId,
            kurzversion: 'for testing',
            langversion: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: -200,
                bildungKompetenzsteinChange: +1,
            ),
            requirements: new CardRequirements(
                guthaben: 5000,
                zeitsteine: 4,
            ),
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ShuffleCards::create()->withFixedCardIdOrderForTesting(
                new Pile( pileId: $pileId, cards: [$cardId]),
            ));

        $stream = $this->stream;
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerActivateCard($this->playerId1, $cardId, $cardToTest1))->toBeFalse()
            ->and($actionsCalculatorUnderTest->canPlayerActivateCard($this->playerId1, $cardId, $cardToTest2))->toBeFalse();
    });
});

