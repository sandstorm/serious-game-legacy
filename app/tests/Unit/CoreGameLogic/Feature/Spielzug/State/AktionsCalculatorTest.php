<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\EreignisId;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ModifierId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\PileId;

beforeEach(function () {
    $this->setupBasicGame();
});

test('welche Spielzüge hat player zur Verfügung', function () {

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1')
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($this->players[0])[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC

    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], array_shift($this->cardsBildung)->getId(), $this->pileIdBildung));
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], array_shift($this->cardsBildung)->getId(), $this->pileIdBildung)
            ->withEreignis(new EreignisId("EVENT:OmaKrank")));
    $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);

    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($this->players[0]))[0]->id)->toBe(ModifierId::AUSSETZEN)
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($this->players[0]))->toBeEmpty()
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($this->players[1])[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC
});

describe('canPlayerActivateCard', function () {

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

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction1))->toBeTrue()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction2))->toBeTrue();
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

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction1))->toBeFalse()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction2))->toBeFalse();
    });
});

