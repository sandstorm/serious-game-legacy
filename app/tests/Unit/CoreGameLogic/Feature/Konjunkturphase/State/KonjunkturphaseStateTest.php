<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

describe('calculateInitialZeitsteineForPlayers', function () {
    it('calculates the correct number for 2,3 and 4 players', function (int $numberOfPlayers) {
        /** @var TestCase $this */
        $this->setupBasicGame($numberOfPlayers);
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualNumbers = KonjunkturphaseState::calculateInitialZeitsteineForPlayers($gameEvents);
        $expectedNumber = match($numberOfPlayers) {
            2 => Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS ,
            3, 4 => Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_THREE_OR_FOUR_PLAYERS
        };
        expect(array_shift($actualNumbers)->zeitsteine)->toBe($expectedNumber)
            ->and(array_shift($actualNumbers)->zeitsteine)->toBe($expectedNumber);
    })->with([2, 3, 4]);
});

describe('isConditionForEndOfKonjunkturphaseMet', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns false if all players have Zeitsteine', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::isConditionForEndOfKonjunkturphaseMet($gameEvents))->toBeFalse();
    });

    it('returns false if at least one player has Zeitsteine', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS +1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);

        $this->coreGameLogic->handle(
            $this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE)
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::isConditionForEndOfKonjunkturphaseMet($gameEvents))->toBeFalse();
    });

    it('returns true if at no player has Zeitsteine', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS +1,
                ),
            ),
            "cardToRemoveZeitsteine2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS +1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);

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

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::isConditionForEndOfKonjunkturphaseMet($gameEvents))->toBeTrue();
    });
});

describe('hasCurrentKonjunkturphaseEnded', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns false if the current Konjunkturphase has not ended', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasCurrentKonjunkturphaseEnded($gameEvents))
            ->toBeFalse('Konjunkturphase should not have ended at the start of the game');

        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
            "cardToRemoveZeitsteine2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);
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


        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasCurrentKonjunkturphaseEnded($gameEvents))
            ->toBeFalse('Konjunkturphase should not have ended before the current player ended their turn');

        $this->coreGameLogic->handle(
            $this->gameId,
            new EndSpielzug($this->players[1])
        );
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create(),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasCurrentKonjunkturphaseEnded($gameEvents))
            ->toBeFalse('Konjunkturphase should not have ended at the start of a new Konjunkturphase');
    });

    it('returns true if the current Konjunkturphase has ended', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
            "cardToRemoveZeitsteine2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);
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

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasCurrentKonjunkturphaseEnded($gameEvents))
            ->toBeTrue('Konjunkturphase should have ended after the last player ended their turn');
    });
});

describe('hasPlayerStartetCurrentKonjunkturphase', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns false if the player has not yet started the Konjunkturphase', function () {
        /** @var TestCase $this */

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[0]))
            ->toBeFalse('Konjunkturphase should not have started for ' . $this->players[0]);

        $this->coreGameLogic->handle(
            $this->gameId,
            StartKonjunkturphaseForPlayer::create(($this->players[0])),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[1]))
            ->toBeFalse('Konjunkturphase should not have started for ' . $this->players[1]);
    });

    it('returns true if the player has started the Konjunkturphase', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle(
            $this->gameId,
            StartKonjunkturphaseForPlayer::create(($this->players[0])),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[0]))
            ->toBeTrue('Konjunkturphase should have started for ' . $this->players[0]);

        $this->coreGameLogic->handle(
            $this->gameId,
            StartKonjunkturphaseForPlayer::create(($this->players[1])),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[1]))
            ->toBeTrue('Konjunkturphase should have started for ' . $this->players[1]);
    });

    it('works in later Konjunkturphasen', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
            "cardToRemoveZeitsteine2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);
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

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create(),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[0]))
            ->toBeFalse('Konjunkturphase should not yet have started for ' . $this->players[0]);

        $this->coreGameLogic->handle(
            $this->gameId,
            StartKonjunkturphaseForPlayer::create(($this->players[0])),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[0]))
            ->toBeTrue('Konjunkturphase should have started for ' . $this->players[0])
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[1]))
            ->toBeFalse('Konjunkturphase should not yet have started for ' . $this->players[1]);

        $this->coreGameLogic->handle(
            $this->gameId,
            StartKonjunkturphaseForPlayer::create(($this->players[1])),
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[1]))
            ->toBeTrue('Konjunkturphase should have started for ' . $this->players[1]);
    });
});

describe('isPlayerReadyForKonjunkturphaseChange', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns false if the player is not ready', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
            "cardToRemoveZeitsteine2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);
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
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(5000)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $this->players[0]))
            ->toBeFalse('The player should not be marked as ready');
    });

    it('returns true if the player is ready', function () {
        /** @var TestCase $this */

        $cardsForTesting = [
            "cardToRemoveZeitsteine" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
            "cardToRemoveZeitsteine2" => new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                pileId: $this->pileIdBildung,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS + 1,
                ),
            ),
        ];
        $this->addCardsOnTopOfPile($cardsForTesting, $this->pileIdBildung);
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
            EnterLebenshaltungskostenForPlayer::create($this->players[0], new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->players[0])
        );

        $this->coreGameLogic->handle(
            $this->gameId,
            MarkPlayerAsReadyForKonjunkturphaseChange::create($this->players[0])
        );

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $this->players[0]))
            ->toBeTrue('The player should be marked as ready');
    });
});
