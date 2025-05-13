<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Enum\Kompetenzbereiche;
use Domain\CoreGameLogic\Dto\Enum\KonjunkturzyklusType;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Kategorie;
use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command\KonjunkturzyklusWechseln;

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
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p2,
        lebensziel: new Lebensziel('Lebensziel XYZ'),
    ));
    $this->coreGameLogic->handle($this->gameId, new LebenszielAuswaehlen(
        playerId: $this->p1,
        lebensziel: new Lebensziel('Lebensziel AAA'),
    ));
});

test('Game logic - Jahr wechseln', function () {
    $this->coreGameLogic->handle($this->gameId, new StartGame([$this->p1, $this->p2]));
    $this->coreGameLogic->handle($this->gameId, new KonjunkturzyklusWechseln(
        konjunkturzyklus: new Konjunkturzyklus(
            KonjunkturzyklusType::AUFSCHWUNG,
            'Beschreibung',
            [
                new Kategorie(Kompetenzbereiche::BILDUNG, 2),
                new Kategorie(Kompetenzbereiche::FREIZEIT, 3),
                new Kategorie(Kompetenzbereiche::INVESTITIONEN, 0),
                new Kategorie(Kompetenzbereiche::ERWEBSEINKOMMEN, 4),
            ],
        ),
    ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GamePhaseState::currentYear($gameStream)->year->value)->toEqual(1);
    expect(GamePhaseState::currentYear($gameStream)->konjunkturzyklus->type)->toEqual(KonjunkturzyklusType::AUFSCHWUNG);
    expect(count(GamePhaseState::currentYear($gameStream)->konjunkturzyklus->categories))->toEqual(4);
    expect(GamePhaseState::currentYear($gameStream)->konjunkturzyklus->categories[0]->name)->toEqual(Kompetenzbereiche::BILDUNG);
    expect(GamePhaseState::currentYear($gameStream)->konjunkturzyklus->categories[0]->zeitSlots)->toEqual(2);

    $this->coreGameLogic->handle($this->gameId, new KonjunkturzyklusWechseln(
        konjunkturzyklus: new Konjunkturzyklus(KonjunkturzyklusType::REZESSION, 'Beschreibung', []),
    ));

    $gameStream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GamePhaseState::currentYear($gameStream)->year->value)->toEqual(2);
    expect(GamePhaseState::currentYear($gameStream)->konjunkturzyklus->type)->toEqual(KonjunkturzyklusType::REZESSION);
});
