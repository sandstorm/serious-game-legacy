<?php

use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\Event\InitializePlayerOrdering;
use Domain\CoreGameLogic\Dto\Event\InitLebenszielEvent;
use Domain\CoreGameLogic\Dto\Event\JahreswechselEvent;
use Domain\CoreGameLogic\Dto\Event\Player\CardActivated;
use Domain\CoreGameLogic\Dto\Event\Player\CardSkipped;
use Domain\CoreGameLogic\Dto\Event\Player\SpielzugWasCompleted;
use Domain\CoreGameLogic\Dto\Event\Player\TriggeredEreignis;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\SpielzugCommandHandler;
use Domain\CoreGameLogic\GameState\AktionsCalculator;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;
use Domain\CoreGameLogic\GameState\LebenszielAccessor;
use Domain\CoreGameLogic\GameState\LeitzinsAccessor;
use Domain\CoreGameLogic\GameState\ModifierCalculator;

beforeEach(function () {
    //$this->forDoingCoreBusinessLogic = new CoreGameLogicApp();
});

test('Event stream can be accessed', function () {
    $stream = GameEvents::fromArray([
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

test('Test Command Handler', function () {
    $commandHandler = new SpielzugCommandHandler();
    $stream = GameEvents::with(
        new InitializePlayerOrdering(
            playerOrdering: [
                new PlayerId('p1'),
                new PlayerId('p2'),
            ]
        ),
    );

    $lebenszielAuswaehlenP1 = new LebenszielAuswaehlen(
        playerId: new PlayerId('p1'),
        lebensziel: new Lebensziel('Lebensziel XYZ'),
    );
    $stream = $commandHandler->handle($lebenszielAuswaehlenP1, $stream);

    expect(LebenszielAccessor::forStream($stream)->forPlayer(new PlayerId('p1'))->lebensziel->name)->toBe('Lebensziel XYZ');

    // catch exception if player tries to set Lebensziel again
    try {
        $stream = $commandHandler->handle($lebenszielAuswaehlenP1, $stream);
    } catch (\RuntimeException $e) {
        expect($e->getCode())->toBe(1746713490);
    }

    $lebenszielAuswaehlenP2 = new LebenszielAuswaehlen(
        playerId: new PlayerId('p2'),
        lebensziel: new Lebensziel('Lebensziel ABC'),
    );

    // catch exception if not the current player tries to set Lebensziel
    try {
        $stream = $commandHandler->handle($lebenszielAuswaehlenP2, $stream);
    } catch (\RuntimeException $e) {
        expect($e->getCode())->toBe(1746700791);
    }

    $spielzugAbschliessen = new SpielzugAbschliessen(
        playerId: new PlayerId('p1'),
    );
    $stream = $commandHandler->handle($spielzugAbschliessen, $stream);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');
});


test('Current Player Handling', function () {
    $stream = GameEvents::fromArray([
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
    $stream = $stream->withAppendedEvents(GameEvents::fromArray([
        new SpielzugWasCompleted(
            player: new PlayerId('p1'),
        )
    ]));
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');

    // Spielerwechsel mit wieder vorn beginnen
    $stream = $stream->withAppendedEvents(GameEvents::fromArray([
        new SpielzugWasCompleted(
            player: new PlayerId('p2'),
        )
    ]));
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    // Player pausieren / ersetzen.
    $stream = $stream->withAppendedEvents(GameEvents::fromArray([
        new InitializePlayerOrdering(
            playerOrdering: [
                new PlayerId('p1'),
                new PlayerId('p3'),
            ]
        ),
        new SpielzugWasCompleted(
            player: new PlayerId('p1'),
        )
    ]));
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p3');
});

test('Init Lebensziel', function () {
    $stream = GameEvents::fromArray([
        new InitializePlayerOrdering(
            playerOrdering: [
                new PlayerId('p1'),
                new PlayerId('p2'),
            ]
        ),
        new InitLebenszielEvent(
            lebensziel : new Lebensziel('Lebensziel XYZ'),
            player: new PlayerId('p1'),
        ),
        new InitLebenszielEvent(
            lebensziel : new Lebensziel('Lebensziel ABC'),
            player: new PlayerId('p2'),
        ),
    ]);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(new PlayerId('p1'))->lebensziel->name)->toBe('Lebensziel XYZ');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(new PlayerId('p2'))->lebensziel->name)->toBe('Lebensziel ABC');
    expect(LebenszielAccessor::forStream($stream)->forPlayer(new PlayerId('p3')))->toBe(null);
});


test('welche Spielzüge hat player zur Verfügung', function () {
    $p1 = new PlayerId('p1');
    $p2 = new PlayerId('p2');
    $stream = GameEvents::fromArray([
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

    $stream = $stream->withAppendedEvents(GameEvents::fromArray([
        // TODO: event missing: new ZeitsteinSetzen(),
        new CardSkipped($p1, new CardId("segellehrer")),
        new CardActivated($p1, new CardId("sozialarbeiterIn")),
        new TriggeredEreignis($p1, new EreignisId("EVENT:OmaKrank")),
        new SpielzugWasCompleted($p1),
    ]));
    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($p1))[0]->value)->toBe("MODIFIER:ausetzen");
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1))->toBeEmpty();
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p2)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC
});

