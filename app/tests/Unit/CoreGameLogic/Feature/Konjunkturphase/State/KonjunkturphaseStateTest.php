<?php

namespace Tests\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;

describe('calculateInitialZeitsteineForPlayers', function () {
    it('calculates the correct number for 2,3 and 4 players', function (int $numberOfPlayers){
        $this->setupBasicGame($numberOfPlayers);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualNumbers = KonjunkturphaseState::calculateInitialZeitsteineForPlayers($stream);
        $expectedNumber = match($numberOfPlayers) {
            2 => 6,
            3 => 5,
            4 => 4,
        };
        expect(array_shift($actualNumbers)->zeitsteine)->toBe($expectedNumber)
            ->and(array_shift($actualNumbers)->zeitsteine)->toBe($expectedNumber);
    })->with([2,3,4]);
});
