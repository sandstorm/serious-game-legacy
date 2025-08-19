<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelAllInsurancesToAvoidInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
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

describe('handleCancelAllInsurancesToAvoidInsolvenzForPlayer', function () {
    // cancels all concluded insurances for a player if they would need to file for Insolvenz
    it('throws an exception if player has a positive balance and therefore is not allowed to cancel insurances', function () {
        /** @var TestCase $this */
        $this->handle(ConcludeInsuranceForPlayer::create($this->getPlayers()[0], InsuranceId::create(1)));
        $this->handle(CancelAllInsurancesToAvoidInsolvenzForPlayer::create($this->getPlayers()[0]));
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
        $this->handle(CancelAllInsurancesToAvoidInsolvenzForPlayer::create($this->getPlayers()[0]));
    })->throws(\RuntimeException::class, "Cannot cancel insurance: Du hast keine Versicherung, die gekündigt werden kann", 1756987783);

    it('cancels all insurances when a player has a negative balance and returns insurance cost', function () {
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
        $this->handle(CancelAllInsurancesToAvoidInsolvenzForPlayer::create($this->getPlayers()[0]));
        $guthabenWithInsuranceCost = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);

        expect(MoneySheetState::doesPlayerHaveThisInsurance($this->getGameEvents(), $this->getPlayers()[0], $this->insurances[0]->id))->toBeFalse()
            ->and($guthabenWithInsuranceCost->value)->toEqual($guthaben->value + 100);
    });
});
