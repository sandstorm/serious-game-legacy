<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\PileFinder;
use Domain\Definitions\Card\ValueObject\PileId;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
});

test('welche Spielzüge hat player zur Verfügung', function () {
    $p1 = PlayerId::fromString('p1');
    $p2 = PlayerId::fromString('p2');

    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(2)
        ->withFixedPlayerIdsForTesting($p1, $p2));

    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $p1,
            $p2
        ]
    ));

    $pileIdBildung = PileId::BILDUNG_PHASE_1;
    $cardsBildung = PileFinder::getCardsIdsForPile($pileIdBildung);
    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile(pileId: $pileIdBildung, cards: $cardsBildung),
        ));

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1')
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1)[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC

    $this->coreGameLogic->handle($this->gameId, new SkipCard($p1, array_shift($cardsBildung), $pileIdBildung));
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($p1, array_shift($cardsBildung), $pileIdBildung)
            ->withEreignis(new EreignisId("EVENT:OmaKrank")));
    $this->coreGameLogic->handle($this->gameId, new EndSpielzug($p1));
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);

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

    it('returns true when player can afford the action', function () {
        $pileId = PileId::BILDUNG_PHASE_1;
        $costOfAction1 = new ResourceChanges(
            guthabenChange: -200,
            bildungKompetenzsteinChange: +1,
        );
        $costOfAction2 = new ResourceChanges(
            guthabenChange: -200,
            bildungKompetenzsteinChange: +1,
        );

        $stream = $this->stream;
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->playerId1, $costOfAction1))->toBeTrue()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->playerId1, $costOfAction2))->toBeTrue();
    });

    it('returns false when player cannot afford the action', function () {
        $pileId = PileId::BILDUNG_PHASE_1;
        $costOfAction1 = new ResourceChanges(
            guthabenChange: -50001,
        );

        $costOfAction2 = new ResourceChanges(
            guthabenChange: -200,
            bildungKompetenzsteinChange: -1,
        );

        $stream = $this->stream;
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->playerId1, $costOfAction1))->toBeFalse()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->playerId1, $costOfAction2))->toBeFalse();
    });
});

