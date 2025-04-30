<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\Event\EventStream;
use Domain\CoreGameLogic\Dto\Event\InitializePlayerOrdering;
use Domain\CoreGameLogic\Dto\Event\JahreswechselEvent;
use Domain\CoreGameLogic\Dto\Event\Player\CardActivated;
use Domain\CoreGameLogic\Dto\Event\Player\CardSkipped;
use Domain\CoreGameLogic\Dto\Event\Player\SpielzugWasCompleted;
use Domain\CoreGameLogic\Dto\Event\Player\TriggeredEreignis;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\GameState\AktionsCalculator;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;
use Domain\CoreGameLogic\GameState\LeitzinsAccessor;
use Domain\CoreGameLogic\GameState\ModifierCalculator;

beforeEach(function () {
    $this->forDoingCoreBusinessLogic = new CoreGameLogicApp();
});

test('Event stream can be accessed', function () {
    $stream = EventStream::fromEvents([
        new JahreswechselEvent(
            year: new CurrentYear(1),
            leitzins: new Leitzins(3)
        ),
        new JahreswechselEvent(
            year: new CurrentYear(1),
            leitzins: new Leitzins(5)
        )
    ]);

    expect(LeitzinsAccessor::forStream($stream)->value)->toBe(5);
});


test('Current Player Handling', function () {
    $stream = EventStream::fromEvents([
        new InitializePlayerOrdering(
            playerOrdering: [
                new PlayerId('p1'),
                new PlayerId('p2'),
            ]
        ),
        new JahreswechselEvent(
            year: new CurrentYear(1),
            leitzins: new Leitzins(3)
        ),
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    // Spielerwechsel
    $stream = $stream->withAdditionalEvents([
        new SpielzugWasCompleted(
            player: new PlayerId('p1'),
        )
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');

    // Spielerwechsel mit wieder vorn beginnen
    $stream = $stream->withAdditionalEvents([
        new SpielzugWasCompleted(
            player: new PlayerId('p2'),
        )
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    // Player pausieren / ersetzen.
    $stream = $stream->withAdditionalEvents([
        new InitializePlayerOrdering(
            playerOrdering: [
                new PlayerId('p1'),
                new PlayerId('p3'),
            ]
        ),
        new SpielzugWasCompleted(
            player: new PlayerId('p1'),
        )
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p3');
});



test('welche Spielzüge hat player zur Verfügung', function () {
    $p1 = new PlayerId('p1');
    $p2 = new PlayerId('p2');
    $stream = EventStream::fromEvents([
        new InitializePlayerOrdering(
            playerOrdering: [
                $p1,
                $p2,
            ]
        ),
        new JahreswechselEvent(
            year: new CurrentYear(1),
            leitzins: new Leitzins(3)
        ),
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC

    $stream = $stream->withAdditionalEvents([
        new ZeitsteinSetzen(),
        new CardSkipped($p1, new CardId("segellehrer")),
        new CardActivated($p1, new CardId("sozialarbeiterIn")),
        new TriggeredEreignis($p1, new EreignisId("EVENT:OmaKrank")),
        new SpielzugWasCompleted($p1),
    ]);
    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($p1))[0]->value)->toBe("MODIFIER:ausetzen");
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1))->toBeEmpty();
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p2)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC
});

