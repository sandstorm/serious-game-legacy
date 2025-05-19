<?php
declare(strict_types=1);

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Pile\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;
use Domain\Definitions\Pile\PileFinder;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');

    $this->cardsBildung = PileFinder::getCardsForBildungAndKarriere();
    $this->pileIdBildung = new PileId(KompetenzbereichEnum::BILDUNG);
    $this->cardsInvest = PileFinder::getCardsForInvestition();
    $this->pileIdInvest = new PileId(KompetenzbereichEnum::INVESTITIONEN);

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

    $this->coreGameLogic->handle($this->gameId, new StartGame(
        playerOrdering: [$this->p1, $this->p2]
    ));

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $this->pileIdBildung, cards: $this->cardsBildung),
            new Pile( pileId: $this->pileIdInvest, cards: $this->cardsInvest),
        ));
});

test('Piles can be shuffled', function () {
    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PileState::topCardForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[0]->value)
        ->and(PileState::topCardForPile($stream, $this->pileIdInvest)->value)->toBe($this->cardsInvest[0]->value);
});

test('Cards can be drawn from piles', function () {
    $this->coreGameLogic->handle(
        $this->gameId,
        new SkipCard($this->p1, $this->cardsBildung[0], $this->pileIdBildung)
    );

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PileState::topCardForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[1]->value)
        ->and(PileState::topCardForPile($stream, $this->pileIdInvest)->value)->toBe($this->cardsInvest[0]->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        new ActivateCard($this->p1, $this->cardsBildung[1], $this->pileIdBildung)
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new SpielzugAbschliessen($this->p1)
    );

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PileState::topCardForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[2]->value)
        ->and(PileState::topCardForPile($stream, $this->pileIdInvest)->value)->toBe($this->cardsInvest[0]->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        new ActivateCard($this->p2, $this->cardsInvest[0], $this->pileIdInvest)
    );

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PileState::topCardForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[2]->value)
        ->and(PileState::topCardForPile($stream, $this->pileIdInvest)->value)->toBe($this->cardsInvest[1]->value);
});

test('Shuffling resets draw counter', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $this->cardsBildung[0], $this->pileIdBildung));
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($this->p1, $this->cardsBildung[1], $this->pileIdBildung));

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PileState::topCardForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[2]->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $this->pileIdBildung, cards: array_reverse($this->cardsBildung)),
            new Pile( pileId: $this->pileIdInvest, cards: $this->cardsInvest),
        ));

    $stream = $this->coreGameLogic->getGameStream($this->gameId);
    expect(PileState::topCardForPile($stream, $this->pileIdBildung)->value)
        ->toBe($this->cardsBildung[count($this->cardsBildung)-1]->value);
});

test('Can only skip top card of pile', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $this->cardsBildung[1], $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Only the top card of the pile can be skipped', 1747325793);

test('Can only activate top card of pile', function () {
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($this->p1, $this->cardsBildung[1], $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Only the top card of the pile can be activated', 1747326086);

test('Cannot activate a card twice', function () {
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($this->p1, $this->cardsBildung[0], $this->pileIdBildung));
    $this->coreGameLogic->handle($this->gameId, new ActivateCard($this->p1, $this->cardsBildung[0], $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Only the top card of the pile can be activated', 1747326086);
