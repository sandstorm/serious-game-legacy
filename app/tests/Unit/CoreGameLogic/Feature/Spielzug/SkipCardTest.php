<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug;


use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\SpielzugCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;
use Tests\TestCase;

@covers(SpielzugCommandHandler::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleSkipCard', function () {

    it('will consume a Zeitstein', function () {
        /** @var TestCase $this */
        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) - 1);
    });

    it('Cannot skip twice', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(
        RuntimeException::class,
        'Cannot skip card: Du kannst nur eine Zeitsteinaktion pro Runde ausführen',
        1747325793);

    it('can only skip when it\'s the player\'s turn', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[1], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(
        RuntimeException::class,
        'Du bist gerade nicht dran',
        1747325793);

    it('throws an error when the player tries to end their Spielzug directly after skipping a card', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId,
            new EndSpielzug($this->players[0]));
    })->throws(
        RuntimeException::class,
        'Cannot end spielzug: Du musst die Karte entweder aktivieren oder zurück auf den Stapel legen',
        1748946243);

    it('cannot skip without a Zeitstein', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        // Check the initial assumption of how many Zeitsteine the player has at the start of the test
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream,
            $this->players[0]))->toBe($this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );

        $this->coreGameLogic->handle($this->gameId,
            DoMinijob::create($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        // confirm that the player has 0 Zeitsteine
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(PlayerState::getZeitsteineForPlayer($stream, $this->players[0]))->toBe(0);

        $this->coreGameLogic->handle($this->gameId,
            new SkipCard(playerId: $this->players[0], categoryId: CategoryId::BILDUNG_UND_KARRIERE));
    })->throws(RuntimeException::class,
        'Cannot skip card: Du hast nicht genug Zeitsteine', 1747325793);

    it("cannot skip card when no free slots are available for this konjunkturphase", function () {
        /** @var TestCase $this */
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition,
        ]);
        $cardsToTest = [
            new KategorieCardDefinition(
                id: new CardId('testcard'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('testcard1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing 1',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('testcard2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing 2',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(0),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsToTest);
        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $events = $stream->findAllOfType(KonjunkturphaseWasChanged::class);
        expect(count($events))->toBe(2);

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, ActivateCard::create(
            playerId: $this->players[0],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(GamePhaseState::hasFreeTimeSlotsForCategory($gameEvents, CategoryId::BILDUNG_UND_KARRIERE))->toBeFalse();

        // this fails, no free slots available
        $this->coreGameLogic->handle($this->gameId, new SkipCard(
            playerId: $this->players[1],
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
        ));
    })->throws(RuntimeException::class,
        'Cannot skip card: Es gibt keine freien Zeitsteinslots mehr',
        1747325793);
});
