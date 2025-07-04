<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

@covers(AktionsCalculator::class);

beforeEach(function () {
    $this->setupBasicGame();
});

describe('canPlayerActivateCard', function () {

    it('returns true when player can afford the action', function () {
        $costOfAction1 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-200),
            bildungKompetenzsteinChange: +1,
        );
        $costOfAction2 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-200),
            bildungKompetenzsteinChange: +1,
        );

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction1))->toBeTrue()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction2))->toBeTrue();
    });

    it('returns false when player cannot afford the action', function () {
        $costOfAction1 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-50001),
        );

        $costOfAction2 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-200),
            bildungKompetenzsteinChange: -1,
        );

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction1))->toBeFalse()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction2))->toBeFalse();
    });
});

