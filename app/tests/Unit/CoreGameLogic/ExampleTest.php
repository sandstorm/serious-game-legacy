<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\UserName;

use function Wwwision\Types\instantiate;

beforeEach(function () {
    $this->forDoingCoreBusinessLogic = new CoreGameLogicApp();
});

test('we can start time recording', function () {
    $user = instantiate(UserName::class, 'Sebastian');
    $this->forDoingCoreBusinessLogic->startTimeRecording($user);
    expect(true)->toBeTrue();
});

test('error if prohibited Username', function () {
    $user = instantiate(UserName::class, 'sandstorm');
    $this->forDoingCoreBusinessLogic->startTimeRecording($user);
})->throws(\RuntimeException::class);
