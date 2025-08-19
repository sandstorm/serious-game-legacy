<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
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

describe('handleConcludeInsurance', function () {
    it('throws an exception when trying to take out an active insurance again', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
        $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->players[0], $this->insurances[0]->id));
    })->throws(\RuntimeException::class, 'Cannot conclude insurance: Versicherung wurde bereits abgeschlossen.');

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

    it('throws an error when the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();
        $cardForTesting = new MinijobCardDefinition(
            id: CardId::fromString("removeZeitsteine1"),
            title: "RemoveZeitsteine1",
            description: "RemoveZeitsteine1",
            resourceChanges: new ResourceChanges(
                guthabenChange: new MoneyAmount(1000),
            ),
        );
        $this->startNewKonjunkturphaseWithCardsOnTop([$cardForTesting]);

        $this->handle(StartKonjunkturphaseForPlayer::create($this->getPlayers()[0]));
        $this->handle(DoMinijob::create($this->getPlayers()[0]));
        $this->handle(ConcludeInsuranceForPlayer::create($this->getPlayers()[0], InsuranceId::create(1)));
    })->throws(\RuntimeException::class, 'Cannot conclude insurance: Du bist insolvent', 1751554652);

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
});
