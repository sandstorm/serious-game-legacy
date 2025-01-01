<?php

use Domain\NameOfCoreDomainX\CoreDomainXApp;
use Domain\NameOfCoreDomainX\DrivenPorts\Mocks\MockLogger;
use Domain\NameOfCoreDomainX\Dto\UserName;

use function Wwwision\Types\instantiate;

beforeEach(function () {
    $this->mockLogger = new MockLogger();
    $this->forDoingCoreBusinessLogic = new CoreDomainXApp(
        forLogging: $this->mockLogger,
    );
});

test('we can start time recording', function () {
    $user = instantiate(UserName::class, "Sebastian");
    $this->forDoingCoreBusinessLogic->startTimeRecording($user);
    expect(true)->toBeTrue();
});

test('error if prohibited Username', function () {
    $user = instantiate(UserName::class, "sandstorm");
    $this->forDoingCoreBusinessLogic->startTimeRecording($user);
})->throws(\RuntimeException::class);
