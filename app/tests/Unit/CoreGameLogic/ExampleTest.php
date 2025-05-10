<?php

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerOrderingWasDefined;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Command\StartNewYear;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;
use Domain\CoreGameLogic\Feature\Jahreswechsel\State\LeitzinsAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = new GameId('game1');
});

test('Event stream can be accessed', function () {
    $stream = GameEvents::fromArray([
        new NewYearWasStarted(
            newYear: new CurrentYear(1),
            leitzins: new Leitzins(3)
        ),
        new NewYearWasStarted(
            newYear: new CurrentYear(1),
            leitzins: new Leitzins(5)
        )
    ]);

    expect(LeitzinsAccessor::forStream($stream)->value)->toBe(5);
});

test('Test Command Handler', function () {
    $this->coreGameLogic->handle(new GameId('game1'), new StartGame(
        playerOrdering: [new PlayerId('p1'), new PlayerId('p2')],
    ));

    $lebenszielAuswaehlenP1 = new LebenszielAuswaehlen(
        playerId: new PlayerId('p1'),
        lebensziel: new Lebensziel('Lebensziel XYZ'),
    );
    $this->coreGameLogic->handle($this->gameId, $lebenszielAuswaehlenP1);
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(LebenszielAccessor::forStream($stream)->forPlayer(new PlayerId('p1'))->lebensziel->name)->toBe('Lebensziel XYZ');

    // catch exception if player tries to set Lebensziel again
    try {
        $this->coreGameLogic->handle($this->gameId, $lebenszielAuswaehlenP1);
    } catch (\RuntimeException $e) {
        expect($e->getCode())->toBe(1746713490);
    }

    $lebenszielAuswaehlenP2 = new LebenszielAuswaehlen(
        playerId: new PlayerId('p2'),
        lebensziel: new Lebensziel('Lebensziel ABC'),
    );

    // catch exception if not the current player tries to set Lebensziel
    try {
        $this->coreGameLogic->handle($this->gameId, $lebenszielAuswaehlenP2);
    } catch (\RuntimeException $e) {
        expect($e->getCode())->toBe(1746700791);
    }

    $spielzugAbschliessen = new SpielzugAbschliessen(
        player: new PlayerId('p1'),
    );
    $this->coreGameLogic->handle($this->gameId, $spielzugAbschliessen);
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');
});


test('Current Player Handling', function () {
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            new PlayerId('p1'),
            new PlayerId('p2'),
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new StartNewYear(
        newYear: new CurrentYear(1),
        leitzins: new Leitzins(3)
    ));

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    // Spielerwechsel
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen(
        player: new PlayerId('p1'),
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p2');

    // Spielerwechsel mit wieder vorn beginnen
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen(
        player: new PlayerId('p2'),
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');

    /* TODO: some problem here??
    // Player pausieren / ersetzen.
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            new PlayerId('p1'),
            new PlayerId('p3'),
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen(
        player: new PlayerId('p1'),
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p3');
    */
});

test('Init Lebensziel', function () {
    $stream = GameEvents::fromArray([
        new PlayerOrderingWasDefined(
            playerOrdering: [
                new PlayerId('p1'),
                new PlayerId('p2'),
            ]
        ),
        new LebenszielChosen(
            lebensziel: new Lebensziel('Lebensziel XYZ'),
            player: new PlayerId('p1'),
        ),
        new LebenszielChosen(
            lebensziel: new Lebensziel('Lebensziel ABC'),
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

    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $p1,
            $p2
        ]
    ));
    $this->coreGameLogic->handle($this->gameId, new StartNewYear(
        newYear: new CurrentYear(1),
        leitzins: new Leitzins(3)
    ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1');
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC

    $this->coreGameLogic->handle($this->gameId, new SkipCard($p1, new CardId("segellehrer")));
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($p1, new CardId("sozialarbeiterIn"), new EreignisId("EVENT:OmaKrank")));
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($p1));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($p1))[0]->value)->toBe("MODIFIER:ausetzen");
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1))->toBeEmpty();
    expect(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p2)[0])->toBeInstanceOf(ZeitsteinSetzen::class); // TODO: VALUE OBJECTS ETC
});
