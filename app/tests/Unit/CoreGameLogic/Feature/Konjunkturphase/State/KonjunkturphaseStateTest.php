<?php

namespace Tests\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

describe('calculateInitialZeitsteineForPlayers', function () {
    it('calculates the correct number for 2,3 and 4 players', function (int $numberOfPlayers){
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
    })->with([2,3,4]);
});

describe('isEndOfKonjunkturphase', function () {
    beforeEach(function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
    });

    it('returns false if all players have Zeitsteine', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::isEndOfKonjunkturphase($gameEvents))->toBeFalse();
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
        expect(KonjunkturphaseState::isEndOfKonjunkturphase($gameEvents))->toBeFalse();
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
        expect(KonjunkturphaseState::isEndOfKonjunkturphase($gameEvents))->toBeTrue();
    });
});
