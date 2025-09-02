<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\FileInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleConcludeInsurance', function () {
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

    it('throws an error when the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $this->handle(StartKonjunkturphaseForPlayer::create($this->getPlayers()[0]));
        $this->handle(DoMinijob::create($this->getPlayers()[0]));
        $this->handle(ConcludeInsuranceForPlayer::create($this->getPlayers()[0], InsuranceId::create(1)));
    })->throws(\RuntimeException::class, 'Cannot conclude insurance: Du bist insolvent', 1751554652);
});
