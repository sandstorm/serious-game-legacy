<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;
use Domain\Definitions\Pile\Enum\PileEnum;
use Domain\Definitions\Pile\PileFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
});

test('welche Spielzüge hat player zur Verfügung', function () {
    $p1 = PlayerId::fromString('p1');
    $p2 = PlayerId::fromString('p2');

    $this->coreGameLogic->handle($this->gameId, new DefinePlayerOrdering(
        playerOrdering: [
            $p1,
            $p2
        ]
    ));

    $pileIdBildung = new PileId(PileEnum::BILDUNG_PHASE_1);
    $cardsBildung = PileFinder::getCardsIdsForPile($pileIdBildung);
    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $pileIdBildung, cards: $cardsBildung),
        ));

    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(CurrentPlayerAccessor::forStream($stream)->value)->toBe('p1')
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1)[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC

    $this->coreGameLogic->handle($this->gameId, new SkipCard($p1, array_shift($cardsBildung), $pileIdBildung));
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($p1, array_shift($cardsBildung), $pileIdBildung)
            ->withEreignis(new EreignisId("EVENT:OmaKrank")));
    $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($p1));
    $stream = $this->coreGameLogic->getGameStream($this->gameId);

    expect(iterator_to_array(ModifierCalculator::forStream($stream)->forPlayer($p1))[0]->id->value)->toBe("MODIFIER:ausetzen")
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p1))->toBeEmpty()
        ->and(AktionsCalculator::forStream($stream)->availableActionsForPlayer($p2)[0])->toBeInstanceOf(ZeitsteinSetzen::class);
    // TODO: VALUE OBJECTS ETC
});
