<?php
declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Moneysheet;

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelAllInsurancesForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleCancelInsuranceForPlayer', function () {
    it('throws an exception when trying to cancel an insurance the player does not have', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, CancelInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
    })->throws(\RuntimeException::class, 'Cannot cancel insurance: Versicherung wurde bereits gekündigt.');

    it('can cancel an active insurance', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId, CancelInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeFalse();
    });
});

describe('handleCancelAllInsurancesForPlayer', function () {
    // cancels all concluded insurances for a player if they would need to file for Insolvenz
    it('throws an exception if player has no negative balance and therefore is not allowed to cancel insurances', function () {
        /** @var TestCase $this */
        $this->handle(ConcludeInsuranceForPlayer::create($this->getPlayers()[0], InsuranceId::create(1)));
        $this->handle(CancelAllInsurancesForPlayer::create($this->getPlayers()[0]));
    })->throws(\RuntimeException::class, "Cannot cancel insurance: Dein Kontostand ist positiv", 1756987783);

    it('throws an exception if player has no insurance concluded to cancel', function () {
        /** @var TestCase $this */
        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->players[0]);
        $cardsForTesting = [
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->add(new MoneyAmount(1))->negate(),
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(DoMinijob::create($this->getPlayers()[0]));
        $this->handle(CancelAllInsurancesForPlayer::create($this->getPlayers()[0]));
    })->throws(\RuntimeException::class, "Cannot cancel insurance: Du hast keine Versicherung, die gekündigt werden kann", 1756987783);

    it('cancels all insurances for almost insolvent player and returns insurance cost', function () {
        /** @var TestCase $this */
        $this->handle(ConcludeInsuranceForPlayer::create($this->getPlayers()[0], InsuranceId::create(1)));

        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->players[0]);
        $cardsForTesting = [
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->negate(),
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new MinijobCardDefinition(
                id: CardId::fromString("removeZeitsteine2"),
                title: "RemoveZeitsteine2",
                description: "RemoveZeitsteine2",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(DoMinijob::create($this->getPlayers()[0]));
        $this->handle(new EndSpielzug($this->getPlayers()[0]));

        $this->handle(DoMinijob::create($this->getPlayers()[1]));
        $this->handle(new EndSpielzug($this->getPlayers()[1]));

        $this->handle(EnterLebenshaltungskostenForPlayer::create(
            $this->getPlayers()[0],
            MoneySheetState::calculateMinimumValueForLebenshaltungskostenForPlayer($this->getGameEvents(), $this->getPlayers()[0])));

        $this->handle(CompleteMoneysheetForPlayer::create($this->getPlayers()[0]));
        $guthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $this->handle(CancelAllInsurancesForPlayer::create($this->getPlayers()[0]));
        $guthabenWithInsuranceCost = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);

        expect(MoneySheetState::doesPlayerHaveThisInsurance($this->getGameEvents(), $this->getPlayers()[0], $this->insurances[0]->id))->toBeFalse()
            ->and($guthabenWithInsuranceCost->value)->toEqual($guthaben->value + 100);
    });

});

describe('handleConcludeInsuranceForPlayer', function () {
    it('throws an exception when trying to take out an active insurance again', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
    })->throws(\RuntimeException::class, 'Cannot conclude insurance: Versicherung wurde bereits abgeschlossen.');

    it('works as expected with multiple players taking out and cancelling insurances simultaneously', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[1]->id))->toBeFalse()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[2]->id))->toBeFalse();

        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[1]->id));
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[2]->id));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[0]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[1]->id))->toBeTrue()
            ->and(MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $this->players[0], $this->insurances[2]->id))->toBeTrue();
    });

    it('removes the correct balance from the player\'s Guthaben', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $initialGuthabenForPlayer1 = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        $initialGuthabenForPlayer2 = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[1]);
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], InsuranceId::create(1)));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $actualGuthabenForPlayer1 = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        $actualGuthabenForPlayer2 = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[1]);
        expect($actualGuthabenForPlayer1->value)->toEqual($initialGuthabenForPlayer1->value - 100)
            ->and($actualGuthabenForPlayer2->value)->toEqual($initialGuthabenForPlayer2->value);
    });

    it('throws an error when the player does not have enough Guthaben', function () {
        /** @var TestCase $this */
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $initialGuthabenForPlayer1 = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        $cardToRemoveGuthaben = new KategorieCardDefinition(
            id: new CardId('cardToRemoveGuthaben'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: '...',
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(-1 * $initialGuthabenForPlayer1->value),
            ),
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardToRemoveGuthaben]);
        $this->coreGameLogic->handle($this->gameId, ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));

        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], InsuranceId::create(1)));

    })->throws(\RuntimeException::class, 'Cannot conclude insurance: Du hast nicht genug Ressourcen', 1751554652);

});
