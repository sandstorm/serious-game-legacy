<?php
declare(strict_types=1);


use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerWasMarkedAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleMarkPlayerAsReadyForKonjunkturphaseChange', function () {

    it('throws an error if it\'s not the end of the konjunkturphase', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );
    })->throws(RuntimeException::class,
        'Cannot mark player as ready: Die aktuelle Konjunkturphase ist noch nicht zu Ende', 1756798041);

    it('throws an error if the player has not completed the money sheet', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );
    })->throws(
        RuntimeException::class,
        "Cannot mark player as ready: Du musst erst das Money Sheet korrekt ausfüllen",
        1756798041);

    it('throws an error if the player has a negative balance', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $initialGuthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->negate(),
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );
    })->throws(\RuntimeException::class, "Cannot mark player as ready: Dein Kontostand ist negativ", 1756798041);

    it('marks the player as ready', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerWasMarkedAsReadyForKonjunkturphaseChange
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) !== null)
            ->toBeTrue('The PlayerWasMarkedAsReadyForKonjunkturphaseChange event should exist for this player');
    });

    it('marks the player as ready when they have a balance of 0', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $initialGuthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    // -Startguthaben + Lebenshaltungskosten -> should end up at 0€ at the end of this Konjunkturphase
                    guthabenChange: $initialGuthaben->negate()->add(new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)),
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[0])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE)
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->players[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect($gameEvents->findLastOrNullWhere(
                fn($event) => $event instanceof PlayerWasMarkedAsReadyForKonjunkturphaseChange
                    && $event->playerId->equals($this->players[0])
                    && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
            ) !== null)
            ->toBeTrue('The PlayerWasMarkedAsReadyForKonjunkturphaseChange event should exist for this player');
    });

});

