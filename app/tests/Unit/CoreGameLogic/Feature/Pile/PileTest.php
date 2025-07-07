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

test('Cards can be drawn from piles', function () {
    $currenTopCardBildung = PileState::topCardIdForPile(
        $this->coreGameLogic->getGameEvents($this->gameId),
        $this->pileIdBildung
    );

    $currenTopCardFreizeit = PileState::topCardIdForPile(
        $this->coreGameLogic->getGameEvents($this->gameId),
        $this->pileIdFreizeit
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
    );

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $this->pileIdBildung)->value)->not->toBe($currenTopCardBildung->value)
        ->and(PileState::topCardIdForPile($gameEvents, $this->pileIdFreizeit)->value)->toBe($currenTopCardFreizeit->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new EndSpielzug($this->players[0])
    );

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $this->pileIdBildung)->value)->not->toBe($currenTopCardBildung->value)
        ->and(PileState::topCardIdForPile($gameEvents, $this->pileIdFreizeit)->value)->toBe($currenTopCardFreizeit->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[1], CategoryId::SOZIALES_UND_FREIZEIT)
    );

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $this->pileIdBildung)->value)->not->toBe($currenTopCardBildung->value)
        ->and(PileState::topCardIdForPile($gameEvents, $this->pileIdFreizeit)->value)->not->toBe($currenTopCardFreizeit->value);
});

test('Shuffling resets draw counter', function () {
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
            new CardOrder( pileId: $this->pileIdBildung, cards: array_reverse($this->cardIdsBildung)),
            new CardOrder( pileId: $this->pileIdFreizeit, cards: $this->cardIdsFreizeit),
        ));

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $this->pileIdBildung)->value)
        ->toBe($this->cardIdsBildung[count($this->cardIdsBildung)-1]->value);
});

test('Test shuffle event', function () {
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect($gameEvents->findLast(CardsWereShuffled::class)->piles)->toBeArray()
        ->and($gameEvents->findLast(CardsWereShuffled::class)->piles[0]->cards)->toBeArray()
        ->and(count($gameEvents->findLast(CardsWereShuffled::class)->piles))->toBe(10);
});
