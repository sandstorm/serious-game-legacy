<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

beforeEach(function () {
    $this->setupBasicGame();
});

describe('getZeitsteineForPlayer', function () {
    it('returns the correct number', function () {
        $this->coreGameLogic->handle($this->gameId, new SkipCard($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(5)
            ->and(PlayerState::getZeitsteineForPlayer($stream, $this->players[1]))->toBe(6);
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getZeitsteineForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1748432811);
});

describe('getGuthabenForPlayer', function () {
    it('returns the correct number', function () {
        $card1 = new KategorieCardDefinition(
            id: CardId::fromString('buk0'),
            pileId: PileId::BILDUNG_PHASE_1,
            title: 'test1',
            description: 'test',
            resourceChanges: new ResourceChanges(
                guthabenChange: -500
            )
        );
        $card2 = new KategorieCardDefinition(
            id: CardId::fromString('buk1'),
            pileId: PileId::BILDUNG_PHASE_1,
            title: 'test1',
            description: 'test',
            resourceChanges: new ResourceChanges(
                guthabenChange: -100
            )
        );
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => [
                "buk0" => $card1,
                "buk1" => $card2,
            ],
            PileId::FREIZEIT_PHASE_1->value => [],
            PileId::JOBS_PHASE_1->value => [],
        ]);

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream, $this->players[0]))->toEqual(49500)
            ->and(PlayerState::getGuthabenForPlayer($stream, $this->players[1]))->toEqual(49900);
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getGuthabenForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});

test('getKompetenzenForPlayer', function () {
    $cardToTest = new KategorieCardDefinition(
        id: CardId::fromString('cardToTest'),
        pileId: PileId::BILDUNG_PHASE_1,
        title: 'setup Bildung',
        description: 'test',
        resourceChanges: new ResourceChanges(
            bildungKompetenzsteinChange: +1,
        )
    );
    $this->addCardsOnTopOfPile([$cardToTest], PileId::BILDUNG_PHASE_1);

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(0);
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    // player 1 activates a card that gives them a bildungs kompetenzstein
    $this->coreGameLogic->handle(
        $this->gameId,
        ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

    // player 1
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1);
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(1);

    //player 2 unchanged
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[1])->phases[0]->placedKompetenzsteineBildung)->toBe(0);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[1]))->toBe(0);
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[1], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);

    // change konjunkturphase
    $this->coreGameLogic->handle(
        $this->gameId,
        ChangeKonjunkturphase::create());

    $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
    // kompetenzen are saved for the lebensziel
    expect(PreGameState::lebenszielForPlayer($gameEvents, $this->players[0])->phases[0]->placedKompetenzsteineBildung)->toBe(1);
    expect(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toBe(1);
    // is 0 again because we are in the next konjunkturphase
    expect(PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $this->players[0], CategoryId::BILDUNG_UND_KARRIERE))->toBe(0);
});
