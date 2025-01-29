<?php

use Domain\NameOfCoreDomainX\CoreDomainXApp;
use Domain\NameOfCoreDomainX\Dto\UserName;

use function Wwwision\Types\instantiate;

beforeEach(function () {
    $this->forDoingCoreBusinessLogic = new CoreDomainXApp();
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
