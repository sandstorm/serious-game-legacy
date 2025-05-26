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
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\CoreGameLogic\Feature\Pile\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\Definitions\Card\PileFinder;
use Domain\Definitions\Card\ValueObject\PileEnum;
use Domain\Definitions\Card\ValueObject\PileId;

beforeEach(function () {
    $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
    $this->gameId = GameId::fromString('game1');
    $this->p1 = PlayerId::fromString('p1');
    $this->p2 = PlayerId::fromString('p2');

    $this->pileIdBildung = new PileId(PileEnum::BILDUNG_PHASE_1);
    $this->cardsBildung = PileFinder::getCardsIdsForPile($this->pileIdBildung);
    $this->pileIdFreizeit = new PileId(PileEnum::FREIZEIT_PHASE_1);
    $this->cardsFreizeit = PileFinder::getCardsIdsForPile($this->pileIdFreizeit);

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
            new Pile( pileId: $this->pileIdFreizeit, cards: $this->cardsFreizeit),
        ));
});

test('Piles can be shuffled', function () {
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[0]->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit[0]->value);
});

test('Cards can be drawn from piles', function () {
    $this->coreGameLogic->handle(
        $this->gameId,
        new SkipCard($this->p1, $this->cardsBildung[0], $this->pileIdBildung)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[1]->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit[0]->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->p1, $this->cardsBildung[1], $this->pileIdBildung)
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new EndSpielzug($this->p1)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[2]->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit[0]->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->p2, $this->cardsFreizeit[0], $this->pileIdFreizeit)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[2]->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit[1]->value);
});

test('Shuffling resets draw counter', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $this->cardsBildung[0], $this->pileIdBildung));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->p1, $this->cardsBildung[1], $this->pileIdBildung));

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung[2]->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ShuffleCards::create()->withFixedCardIdOrderForTesting(
            new Pile( pileId: $this->pileIdBildung, cards: array_reverse($this->cardsBildung)),
            new Pile( pileId: $this->pileIdFreizeit, cards: $this->cardsFreizeit),
        ));

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)
        ->toBe($this->cardsBildung[count($this->cardsBildung)-1]->value);
});

test('Can only skip top card of pile', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $this->cardsBildung[1], $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Only the top card of the pile can be skipped', 1747325793);

test('Can only activate top card of pile', function () {
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->p1, $this->cardsBildung[1], $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Only the top card of the pile can be activated', 1747326086);

test('Cannot activate a card twice', function () {
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->p1, $this->cardsBildung[0], $this->pileIdBildung));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->p1, $this->cardsBildung[0], $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Only the top card of the pile can be activated', 1747326086);

test('End of pile is reached', function () {
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung[0]->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung[0]->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));
})->throws(\RuntimeException::class, 'Card index (3) out of bounds for pile ([PileId: Bildung & Karriere | Phase 1])', 1748003108);

test('End of pile is reached and its re shuffled', function () {
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung[0]->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung[1]->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung[2]->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));

    // skipping a card of another pile is possible
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdFreizeit);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdFreizeit));

    // shuffle cards to avoid end of pile
    $this->coreGameLogic->handle($this->gameId, ShuffleCards::create($this->pileIdBildung));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung[0]->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->p1, $topCard, $this->pileIdBildung));


});

test('Test shuffle event', function () {
    $this->coreGameLogic->handle($this->gameId, ShuffleCards::create());
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect($stream->findLast(CardsWereShuffled::class)->piles)->toBeArray();
    expect($stream->findLast(CardsWereShuffled::class)->piles[0]->cards)->toBeArray();
    expect(count($stream->findLast(CardsWereShuffled::class)->piles))->toBe(9);
});
