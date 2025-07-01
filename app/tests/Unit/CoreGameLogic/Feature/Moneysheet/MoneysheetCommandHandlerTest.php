<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet;


use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});


describe('handleCancelInsuranceForPlayer', function () {
    it('throws an exception when trying to cancel an insurance the player does not have', function () {
        $this->coreGameLogic->handle($this->gameId, CancelInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
    })->throws(\RuntimeException::class, 'Cannot cancel insurance that was not concluded.');

    it('can cancel an active insurance', function () {
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId, CancelInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeFalse();
    });
});

describe('handleConcludeInsuranceForPlayer', function () {
    it('throws an exception when trying to take out an active insurance again', function () {
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
    })->throws(\RuntimeException::class, 'Cannot conclude insurance that was already concluded.');

    it('works as expected with multiple players taking out and cancelling insurances simultaneously', function () {
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[1]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[2]->id))->toBeFalse();

        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[1]->id));
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[2]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[1]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[2]->id))->toBeTrue();
    });
});
