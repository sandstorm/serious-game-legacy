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
use Domain\CoreGameLogic\Feature\Konjunkturphase\KonjunkturphaseCommandHandler;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;

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
    $handler = new KonjunkturphaseCommandHandler();
    $this->coreGameLogic->handle($this->gameId, StartGame::create());

    // year 1
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    $year1 = GamePhaseState::currentKonjunkturphase($gameStream);
    expect($year1->year->value)->toEqual(1);

    // year 2
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    $year2 = GamePhaseState::currentKonjunkturphase($gameStream);
    expect($year2->year->value)->toEqual(2);

    $idsOfPastKonjunkturphasen = $handler->getIdsOfPastKonjunkturphasen($gameStream);
    expect(count($idsOfPastKonjunkturphasen))->toEqual(2);
    expect($idsOfPastKonjunkturphasen[0])->toEqual($year1->id)
        ->and($idsOfPastKonjunkturphasen[1])->toEqual($year2->id);

    // year 3
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    $year3 = GamePhaseState::currentKonjunkturphase($gameStream);
    expect($year3->year->value)->toEqual(3);

    // check that the years have different konjunkturphase
    expect($year1->id)->not->toEqual($year2->id)
        ->and($year2->id)->not->toEqual($year3->id)
        ->and($year1->id)->not->toEqual($year3->id);

    $idsOfPastKonjunkturphasen = $handler->getIdsOfPastKonjunkturphasen($gameStream);
    expect(count($idsOfPastKonjunkturphasen))->toEqual(0);

    // year 4
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    $year4 = GamePhaseState::currentKonjunkturphase($gameStream);
    expect($year4->year->value)->toEqual(4);

    $idsOfPastKonjunkturphasen = $handler->getIdsOfPastKonjunkturphasen($gameStream);
    expect(count($idsOfPastKonjunkturphasen))->toEqual(1);
    expect($year4->id)->toEqual($year4->id);

    $amountOfPastKonjunkturphasen = count(GamePhaseState::idsOfPastKonjunkturphasen($gameStream));
    expect($amountOfPastKonjunkturphasen)->toEqual(4);
});

test('Game logic - change a large amount of years', function() {
    $amountOfYears = 20;
    $this->coreGameLogic->handle($this->gameId, StartGame::create());
    for ($i = 0; $i < $amountOfYears; $i++) {
        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
        $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
        $year = GamePhaseState::currentKonjunkturphase($gameStream);
        expect($year->year->value)->toEqual($i + 1);
    }

    $amountOfPastKonjunkturphasen = count(GamePhaseState::idsOfPastKonjunkturphasen($gameStream));
    expect($amountOfPastKonjunkturphasen)->toEqual(20);
});

test('Game logic - change year with fixed konjunkturphase', function () {
    $this->coreGameLogic->handle($this->gameId, StartGame::create());

    $nextKonjunkturphase = KonjunkturphaseFinder::getAllKonjunkturphasen()[0];
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create()->withFixedKonjunkturphaseForTesting(
        $nextKonjunkturphase
    ));

    $gameStream = $this->coreGameLogic->getGameEvents($this->gameId);
    $year1 = GamePhaseState::currentKonjunkturphase($gameStream);
    expect($year1->year->value)->toEqual(1);
    expect($year1->type)->toEqual($nextKonjunkturphase->type);
    expect($year1->leitzins->value)->toEqual($nextKonjunkturphase->leitzins);

    $konjunkturphase= KonjunkturphaseFinder::findKonjunkturphaseById($year1->id);
    expect($konjunkturphase->kompetenzbereiche)->toEqual($nextKonjunkturphase->kompetenzbereiche);
    expect($konjunkturphase->description)->toEqual($nextKonjunkturphase->description);
});
