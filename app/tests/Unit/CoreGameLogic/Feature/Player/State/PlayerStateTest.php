<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

beforeEach(function () {
    $this->setupBasicGame();
});

describe('getZeitsteineForPlayer', function () {
    it('returns the correct number', function () {
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard($this->player1, array_shift($this->cardsBildung)->getId(), $this->pileIdBildung));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->player1))->toBe(2)
            ->and(PlayerState::getZeitsteineForPlayer($stream, $this->player2))->toBe(3);
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getZeitsteineForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1748432811);
});

describe('getGuthabenForPlayer', function () {
    it('returns the correct number', function () {
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->player1, array_shift($this->cardsBildung)->getId(), $this->pileIdBildung)
                ->withFixedCardDefinitionForTesting(new KategorieCardDefinition(
                    id: CardId::fromString('buk0'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'test1',
                    description: 'test',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: -500
                    ))));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->player1));
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->player2, array_shift($this->cardsBildung)->getId(), $this->pileIdBildung)
                ->withFixedCardDefinitionForTesting(new KategorieCardDefinition(
                    id: CardId::fromString('buk1'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'test1',
                    description: 'test',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: -100
                    ))));
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getGuthabenForPlayer($stream, $this->player1))->toBe(49500)
            ->and(PlayerState::getGuthabenForPlayer($stream, $this->player2))->toBe(49900);
    });

    it('Throws an exception if the player does not exist', function () {
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        PlayerState::getGuthabenForPlayer($stream, PlayerId::fromString('doesNotExist'));
    })->throws(\RuntimeException::class, 'Player doesNotExist does not exist', 1747827331);
});

