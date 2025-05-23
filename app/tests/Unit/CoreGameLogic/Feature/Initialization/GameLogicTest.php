<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command\KonjunkturzyklusWechseln;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\KonjunkturzykluswechselCommandHandler;
use Domain\Definitions\Konjunkturzyklus\KonjunkturzyklusFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');

    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($this->p1, $this->p2));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p1,
        name: 'Player 1a',
    ));
    $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
        playerId: $this->p2,
        name: 'Player 2',
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p2,
        lebensziel: new LebenszielId(1),
    ));
    $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
        playerId: $this->p1,
        lebensziel: new LebenszielId(2),
    ));
});

test('Game logic - change year randomly', function () {
    $handler = new KonjunkturzykluswechselCommandHandler();
    $this->coreGameLogic->handle($this->gameId, new StartGame([$this->p1, $this->p2]));

    // year 1
    $this->coreGameLogic->handle($this->gameId, KonjunkturzyklusWechseln::create());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year1 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year1->year->value)->toEqual(1);

    // year 2
    $this->coreGameLogic->handle($this->gameId, KonjunkturzyklusWechseln::create());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year2 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year2->year->value)->toEqual(2);

    $idsOfPastKonjunkturzyklen = $handler->getIdsOfPastKonjunkturzyklen($gameStream);
    expect(count($idsOfPastKonjunkturzyklen))->toEqual(2);
    expect($idsOfPastKonjunkturzyklen[0])->toEqual($year1->id)
        ->and($idsOfPastKonjunkturzyklen[1])->toEqual($year2->id);

    // year 3
    $this->coreGameLogic->handle($this->gameId, KonjunkturzyklusWechseln::create());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year3 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year3->year->value)->toEqual(3);

    // check that the years have different konjunkturzyklus
    expect($year1->id)->not->toEqual($year2->id)
        ->and($year2->id)->not->toEqual($year3->id)
        ->and($year1->id)->not->toEqual($year3->id);

    $idsOfPastKonjunkturzyklen = $handler->getIdsOfPastKonjunkturzyklen($gameStream);
    expect(count($idsOfPastKonjunkturzyklen))->toEqual(0);

    // year 4
    $this->coreGameLogic->handle($this->gameId, KonjunkturzyklusWechseln::create());
    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year4 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year4->year->value)->toEqual(4);

    $idsOfPastKonjunkturzyklen = $handler->getIdsOfPastKonjunkturzyklen($gameStream);
    expect(count($idsOfPastKonjunkturzyklen))->toEqual(1);
    expect($year4->id)->toEqual($year4->id);

    $amountOfPastKonjunkturzyklen = count(GamePhaseState::idsOfPastKonjunkturzyklen($gameStream));
    expect($amountOfPastKonjunkturzyklen)->toEqual(4);
});

test('Game logic - change a large amount of years', function() {
    $amountOfYears = 20;
    $this->coreGameLogic->handle($this->gameId, new StartGame([$this->p1, $this->p2]));
    for ($i = 0; $i < $amountOfYears; $i++) {
        $this->coreGameLogic->handle($this->gameId, KonjunkturzyklusWechseln::create());
        $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
        $year = GamePhaseState::currentKonjunkturzyklus($gameStream);
        expect($year->year->value)->toEqual($i + 1);
    }

    $amountOfPastKonjunkturzyklen = count(GamePhaseState::idsOfPastKonjunkturzyklen($gameStream));
    expect($amountOfPastKonjunkturzyklen)->toEqual(20);
});

test('Game logic - change year with fixed konjunkturzyklus', function () {
    $this->coreGameLogic->handle($this->gameId, new StartGame([$this->p1, $this->p2]));

    $nextKonjunkturZyklus = KonjunkturzyklusFinder::getAllKonjunkturzyklen()[0];
    $this->coreGameLogic->handle($this->gameId, KonjunkturzyklusWechseln::createWithFixedKonjunkturzyklusForTesting(
        $nextKonjunkturZyklus
    ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    $year1 = GamePhaseState::currentKonjunkturzyklus($gameStream);
    expect($year1->year->value)->toEqual(1);
    expect($year1->type)->toEqual($nextKonjunkturZyklus->type);
    expect($year1->leitzins->value)->toEqual($nextKonjunkturZyklus->leitzins);

    $konjunkturZyklus = KonjunkturzyklusFinder::findKonjunkturZyklusById($year1->id);
    expect($konjunkturZyklus->kompetenzbereiche)->toEqual($nextKonjunkturZyklus->kompetenzbereiche);
    expect($konjunkturZyklus->description)->toEqual($nextKonjunkturZyklus->description);
});
