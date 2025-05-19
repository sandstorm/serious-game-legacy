<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\State\GuthabenState;
use Domain\CoreGameLogic\Feature\Initialization\State\ZeitsteineState;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\Definitions\Cards\Model\CardDefinition;
use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;
use Domain\Definitions\Pile\Enum\PileEnum;
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
    $cardsSozialesAndFreizeit = [new CardDefinition(
        id: new CardId('testId'),
        pileId: new PileId(PileEnum::FREIZEIT_PHASE_1),
        kurzversion: 'Ehrenamtliches Engagement',
        langversion: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
        resourceChanges: new ResourceChanges(
            guthabenChange: -500,
            zeitsteineChange: -1,
            freizeitKompetenzsteinChange: +1,
        ),
        requirements: new CardRequirements(
            guthaben: 500,
        ),
    )];
    $pileIdSozialesAndFreizeit = new PileId(PileEnum::FREIZEIT_PHASE_1);

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $pileIdSozialesAndFreizeit, cards: [$cardsSozialesAndFreizeit[0]->id]),
        ));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GuthabenState::forPlayer($stream, $p1)->value)->toBe(50000)
        ->and(ZeitsteineState::forPlayer($stream, $p1)->value)->toBe(3);
    //</editor-fold>

    //<editor-fold desc="modify guthaben">
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($p1, $cardsSozialesAndFreizeit[0]->id, $pileIdSozialesAndFreizeit)
            ->withEreignis(new EreignisId("EVENT:Lotteriegewinn"))
            ->withFixedCardDefinitionForTesting($cardsSozialesAndFreizeit[0])
    );
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(GuthabenState::forPlayer($stream, $p1)->value)->toBe(50500)
        ->and(ZeitsteineState::forPlayer($stream, $p1)->value)->toBe(2);
    //</editor-fold>
});

