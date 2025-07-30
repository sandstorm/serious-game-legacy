<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame(2);
});

test('Cards can be drawn from piles', function () {
    /** @var TestCase $this */
    $pileIdBildungUndKarriere = new PileId(CategoryId::BILDUNG_UND_KARRIERE, LebenszielPhaseId::PHASE_1);
    $pileIdSozialesUndFreizeit = new PileId(CategoryId::SOZIALES_UND_FREIZEIT, LebenszielPhaseId::PHASE_1);
    $currenTopCardBildung = PileState::topCardIdForPile(
        $this->coreGameLogic->getGameEvents($this->gameId),
        $pileIdBildungUndKarriere,
    );

    $currenTopCardFreizeit = PileState::topCardIdForPile(
        $this->coreGameLogic->getGameEvents($this->gameId),
        $pileIdSozialesUndFreizeit,
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
    );

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $pileIdBildungUndKarriere)->value)->not->toBe($currenTopCardBildung->value)
        ->and(PileState::topCardIdForPile($gameEvents, $pileIdSozialesUndFreizeit)->value)->toBe($currenTopCardFreizeit->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
    );

    $this->coreGameLogic->handle(
        $this->gameId,
        new EndSpielzug($this->players[0])
    );

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $pileIdBildungUndKarriere)->value)->not->toBe($currenTopCardBildung->value)
        ->and(PileState::topCardIdForPile($gameEvents, $pileIdSozialesUndFreizeit)->value)->toBe($currenTopCardFreizeit->value);

    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[1], CategoryId::SOZIALES_UND_FREIZEIT)
    );

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $pileIdBildungUndKarriere)->value)->not->toBe($currenTopCardBildung->value)
        ->and(PileState::topCardIdForPile($gameEvents, $pileIdSozialesUndFreizeit)->value)->not->toBe($currenTopCardFreizeit->value);
});

test('Shuffling resets draw counter', function () {
    /** @var TestCase $this */
    $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
    $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create()->withFixedCardOrderForTesting());

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PileState::topCardIdForPile($gameEvents, $this->pileIdBildungUndKarriere)->value)
        ->toBe($this->cardIdsBildung[count($this->cardIdsBildung)-1]->value);
})->todo('fix or remove');

test('Test shuffle event', function () {
    $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect($gameEvents->findLast(CardsWereShuffled::class)->piles)->toBeArray()
        ->and($gameEvents->findLast(CardsWereShuffled::class)->piles[0]->getCardIds())->toBeArray()
        ->and(count($gameEvents->findLast(CardsWereShuffled::class)->piles))->toBe(6);
})->todo('this needs to be refactored/rewritten/removed?');
