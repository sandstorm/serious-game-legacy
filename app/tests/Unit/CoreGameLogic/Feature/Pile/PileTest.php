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
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

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
        new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk1"]->id->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit["suf0"]->id->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
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
        ActivateCard::create($this->players[1], CategoryId::SOZIALES_UND_FREIZEIT)
    );

    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($stream, $this->pileIdBildung)->value)->toBe($this->cardsBildung["buk2"]->id->value)
        ->and(PileState::topCardIdForPile($stream, $this->pileIdFreizeit)->value)->toBe($this->cardsFreizeit["suf1"]->id->value);
});

test('Shuffling resets draw counter', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

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

test('Test shuffle event', function () {
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $stream = $this->coreGameLogic->getGameEvents($this->gameId);
    expect($stream->findLast(CardsWereShuffled::class)->piles)->toBeArray();
    expect($stream->findLast(CardsWereShuffled::class)->piles[0]->cards)->toBeArray();
    expect(count($stream->findLast(CardsWereShuffled::class)->piles))->toBe(10);
});
