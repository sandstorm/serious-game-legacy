<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\GuthabenState;
use Domain\CoreGameLogic\Feature\Initialization\State\ZeitsteineState;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;
use Domain\Definitions\Pile\PileFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
});


test('wie viel Guthaben hat Player zur Verfügung', function () {
    //<editor-fold desc="initialize guthaben">
    $p1 = PlayerId::fromString('p1');
    $p2 = PlayerId::fromString('p2');
    $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
        numberOfPlayers: 2,
    )->withFixedPlayerIdsForTesting($p1, $p2));
    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $p1,
            $p2,
        ]
    ));
    $this->cardsSozialesAndFreizeit = PileFinder::getCardsForSozialesAndFreizeit();
    $this->pileIdSozialesAndFreizeit = new PileId(KompetenzbereichEnum::FREIZEIT);

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $this->pileIdSozialesAndFreizeit, cards: $this->cardsSozialesAndFreizeit),
        ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GuthabenState::forPlayer($stream, $p1)->value)->toBe(50000)
        ->and(ZeitsteineState::forPlayer($stream, $p1)->value)->toBe(3);
    //</editor-fold>

    //<editor-fold desc="modify guthaben">
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($p1, $this->cardsSozialesAndFreizeit[0], $this->pileIdSozialesAndFreizeit, new EreignisId("EVENT:Lotteriegewinn")));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GuthabenState::forPlayer($stream, $p1)->value)->toBe(50500)
        ->and(ZeitsteineState::forPlayer($stream, $p1)->value)->toBe(2);
    //</editor-fold>
});

