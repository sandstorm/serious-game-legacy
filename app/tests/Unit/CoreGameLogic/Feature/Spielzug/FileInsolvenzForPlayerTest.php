<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\FileInsolvenzForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasFiledForInsolvenz;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

function setupRegularKonjunkturphaseEnd(TestCase $testCase): void
{
    /** @var TestCase $this */
    $cardsForTesting = [
        new KategorieCardDefinition(
            id: CardId::fromString("removeZeitsteine1"),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: "RemoveZeitsteine1",
            description: "RemoveZeitsteine1",
            resourceChanges: new ResourceChanges(
                zeitsteineChange: -1 * $testCase->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
            ),
        ),
        new KategorieCardDefinition(
            id: CardId::fromString("removeZeitsteine2"),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: "RemoveZeitsteine2",
            description: "RemoveZeitsteine2",
            resourceChanges: new ResourceChanges(
                zeitsteineChange: -1 * $testCase->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
            ),
        ),
    ];
    $testCase->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

    $testCase->handle(ActivateCard::create($testCase->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
    $testCase->handle(new EndSpielzug($testCase->getPlayers()[0]));

    $testCase->handle(ActivateCard::create($testCase->getPlayers()[1], CategoryId::BILDUNG_UND_KARRIERE));
    $testCase->handle(new EndSpielzug($testCase->getPlayers()[1]));

    $testCase->handle(EnterLebenshaltungskostenForPlayer::create(
        $testCase->getPlayers()[0],
        new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));
}

describe('FileInsolvenzForPlayer', function () {
    it('throws an error if the player has a positive balance', function () {
        /** @var TestCase $this */
        setupRegularKonjunkturphaseEnd($this);
        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->getPlayers()[0])
        );

        $this->coreGameLogic->handle($this->gameId, FileInsolvenzForPlayer::create($this->getPlayers()[0]));
    })->throws(
        RuntimeException::class,
        'Cannot file for Insolvenz: Dein Kontostand ist positiv',
        1756801753);

    it('throws an error if the player has not completed their money sheet', function () {
        /** @var TestCase $this */
        setupRegularKonjunkturphaseEnd($this);

        $this->coreGameLogic->handle($this->gameId, FileInsolvenzForPlayer::create($this->getPlayers()[0]));
    })->throws(
        RuntimeException::class,
        'Cannot file for Insolvenz: Du musst erst das Money Sheet korrekt ausfüllen',
        1756801753);

    it('throws an error if the player has investments that can be sold', function () {
        /** @var TestCase $this */
        $this->setupBasicGame();
        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 2,
                ),
            ),
            new KategorieCardDefinition(
                id: CardId::fromString("removeZeitsteine2"),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: "RemoveZeitsteine2",
                description: "RemoveZeitsteine2",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[0]));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->getPlayers()[1], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[1]));

        // Buy as many shares as we can with our Guthaben
        $this->coreGameLogic->handle($this->gameId, BuyInvestmentsForPlayer::create(
            $this->getPlayers()[0],
            InvestmentId::MERFEDES_PENZ,
            intval(floor($initialGuthaben->value / InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(),
                    InvestmentId::MERFEDES_PENZ)->value))));
        $this->coreGameLogic->handle($this->gameId,
            DontSellInvestmentsForPlayer::create($this->getPlayers()[1], InvestmentId::MERFEDES_PENZ));

        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[0]));

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create(
                $this->getPlayers()[0],
                MoneySheetState::calculateMinimumValueForLebenshaltungskostenForPlayer($this->getGameEvents(),
                    $this->getPlayers()[0])));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->getPlayers()[0])
        );

        $this->coreGameLogic->handle($this->gameId, FileInsolvenzForPlayer::create($this->getPlayers()[0]));
    })->throws(
        RuntimeException::class,
        'Cannot file for Insolvenz: Du hast noch Geldanlagen, die du verkaufen kannst',
        1756801753);

    it('throws an error if the player has an active insurance that could be cancelled', function () {
        /** @var TestCase $this */

        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->negate()->add(new MoneyAmount(1000)),
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: CardId::fromString("removeZeitsteine2"),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: "RemoveZeitsteine2",
                description: "RemoveZeitsteine2",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            ConcludeInsuranceForPlayer::create($this->getPlayers()[0], InsuranceId::create(1)));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[0]));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->getPlayers()[1], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[1]));

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->getPlayers()[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->getPlayers()[0])
        );

        $this->coreGameLogic->handle($this->gameId, FileInsolvenzForPlayer::create($this->getPlayers()[0]));
    })->throws(
        RuntimeException::class,
        'Cannot file for Insolvenz: Du hast noch Versicherungen, die du kündigen kannst',
        1756801753);

    it('works if player fulfills all requirements', function () {
        /** @var TestCase $this */

        $initialGuthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: CardId::fromString("removeZeitsteine1"),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: "RemoveZeitsteine1",
                description: "RemoveZeitsteine1",
                resourceChanges: new ResourceChanges(
                    guthabenChange: $initialGuthaben->negate(),
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: CardId::fromString("removeZeitsteine2"),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: "RemoveZeitsteine2",
                description: "RemoveZeitsteine2",
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[0]));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->getPlayers()[1], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->getPlayers()[1]));

        $this->coreGameLogic->handle($this->gameId,
            EnterLebenshaltungskostenForPlayer::create($this->getPlayers()[0],
                new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)));

        $this->coreGameLogic->handle(
            $this->gameId,
            CompleteMoneysheetForPlayer::create($this->getPlayers()[0])
        );

        $guthabenBeforeInsolvenz = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->getPlayers()[0]);

        $this->coreGameLogic->handle($this->gameId, FileInsolvenzForPlayer::create($this->getPlayers()[0]));
        $eventsAfterInsolvenz = $this->getGameEvents();
        $playerFiledForInsolvenzEvent = $eventsAfterInsolvenz->findLast(PlayerHasFiledForInsolvenz::class);
        expect($playerFiledForInsolvenzEvent->getPlayerId()->equals($this->getPlayers()[0]))->toBeTrue()
            ->and($playerFiledForInsolvenzEvent->getYear()->equals(KonjunkturphaseState::getCurrentYear($eventsAfterInsolvenz)))
            ->and($playerFiledForInsolvenzEvent->getResourceChanges($this->getPlayers()[0])->guthabenChange->value)->toEqual($guthabenBeforeInsolvenz->negate()->value);
    });
});
