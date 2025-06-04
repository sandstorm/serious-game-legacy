<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\Definitions\Card\PileFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;

beforeEach(function () {
    $this->setupBasicGame(2);

    $this->cardIdsBildung = PileFinder::getCardsIdsForPile($this->pileIdBildung);
    $this->cardIdsFreizeit = PileFinder::getCardsIdsForPile($this->pileIdFreizeit);
});

test('Piles can be shuffled', function () {
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk0"]->id->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit["suf0"]->id->value);
});

test('Cards can be drawn from piles', function () {
    $this->coreGameLogic->handle(
        $this->gameId,
        new SkipCard($this->players[0], $this->cardsBildung["buk0"]->id, $this->pileIdBildung, CategoryEnum::BILDUNG)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk1"]->id->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit["suf0"]->id->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], $this->cardsBildung["buk1"]->id, $this->pileIdBildung, CategoryEnum::BILDUNG)
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new EndSpielzug($this->players[0])
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk2"]->id->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit["suf0"]->id->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[1], $this->cardsFreizeit["suf0"]->id, $this->pileIdFreizeit, CategoryEnum::FREIZEIT)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk2"]->id->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit["suf1"]->id->value);
});

test('Shuffling resets draw counter', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $this->cardsBildung["buk0"]->id, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], $this->cardsBildung["buk1"]->id, $this->pileIdBildung, CategoryEnum::BILDUNG));

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk2"]->id->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
            new CardOrder( pileId: $this->pileIdBildung, cards: array_reverse($this->cardIdsBildung)),
            new CardOrder( pileId: $this->pileIdFreizeit, cards: $this->cardIdsFreizeit),
        ));

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)
        ->toBe($this->cardIdsBildung[count($this->cardIdsBildung)-1]->value);
});
test('Cannot activate a card twice', function () {
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], $this->cardsBildung["buk0"]->id, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], $this->cardsBildung["buk0"]->id, $this->pileIdBildung, CategoryEnum::BILDUNG));
})->throws(\RuntimeException::class, 'Cannot activate Card: Nur die oberste Karte auf einem Stapel kann gespielt werden', 1748951140);

test('End of pile is reached', function () {
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung["buk0"]->id->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung["buk0"]->id->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));
})->throws(\RuntimeException::class, 'Card index (3) out of bounds for pile (Bildung & Karriere | Phase 1)', 1748003108);

test('End of pile is reached and its re shuffled', function () {
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung["buk0"]->id->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung["buk1"]->id->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdBildung);
    expect($topCard->value)->toBe($this->cardsBildung["buk2"]->id->value);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdBildung, CategoryEnum::BILDUNG));

    // skipping a card of another pile is possible
    $topCard = PileState::topCardIdForPile($this->coreGameLogic->getGameEvents($this->gameId), $this->pileIdFreizeit);
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], $topCard, $this->pileIdFreizeit, CategoryEnum::BILDUNG));
});

test('Test shuffle event', function () {
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect($stream->findLast(CardsWereShuffled::class)->piles)->toBeArray();
    expect($stream->findLast(CardsWereShuffled::class)->piles[0]->cards)->toBeArray();
    expect(count($stream->findLast(CardsWereShuffled::class)->piles))->toBe(9);
});
