<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Tests\DomainTestCase;

beforeEach(function () {
    $this->setupBasicGame();
});

describe('canPlayerActivateCard', function () {

    it('returns true when player can afford the action', function () {
        $costOfAction1 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-200),
            bildungKompetenzsteinChange: +1,
        );
        $costOfAction2 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-200),
            bildungKompetenzsteinChange: +1,
        );

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction1))->toBeTrue()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction2))->toBeTrue();
    });

    it('returns false when player cannot afford the action', function () {
        $costOfAction1 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-50001),
        );

        $costOfAction2 = new ResourceChanges(
            guthabenChange: new MoneyAmount(-200),
            bildungKompetenzsteinChange: -1,
        );

        $stream = $this->coreGameLogic->getGameEvents($this->gameId);
        $actionsCalculatorUnderTest = AktionsCalculator::forStream($stream);
        expect($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction1))->toBeFalse()
            ->and($actionsCalculatorUnderTest->canPlayerAffordAction($this->players[0], $costOfAction2))->toBeFalse();
    });
});

describe('getModifiedResourceChangesForCard', function () {
    it('applies the card costs of the current Konjunkturphase per category until the next Konjunkturphase', function () {
        /** @var DomainTestCase $this */
        $bildungCard = new KategorieCardDefinition(
            id: new CardId('bildungCard'),
            categoryId: CategoryId::BILDUNG_UND_KARRIERE,
            title: 'for testing',
            description: 'for testing',
            resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1000)),
        );
        $sozialesCard = new KategorieCardDefinition(
            id: new CardId('sozialesCard'),
            categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
            title: 'for testing',
            description: 'for testing',
            resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1000)),
        );
        $normalKonjunkturphase = $this->getKonjunkturphaseDefinition();
        $konjunkturphaseWithModifiedCosts = $this->konjunkturphaseDefinitionWithModifiers(
            KonjunkturphasenId::create(2),
            [
                ...$normalKonjunkturphase->modifierIds,
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
            ],
            // different values, so a modifier applied to the wrong category is noticed
            new ModifierParameters(
                modifyKostenBildungUndKarrierePercent: 150,
                modifyKostenSozialesUndFreizeitPercent: 50,
            ),
        );
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([$normalKonjunkturphase, $konjunkturphaseWithModifiedCosts]);

        $this->setKonjunkturphaseDefinition($konjunkturphaseWithModifiedCosts);
        $this->startNewKonjunkturphaseWithCardsOnTop([$bildungCard, $sozialesCard]);
        $calculator = AktionsCalculator::forStream($this->getGameEvents());
        expect($calculator->getModifiedResourceChangesForCard($bildungCard)->guthabenChange)->toEqual(new MoneyAmount(-1500))
            ->and($calculator->getModifiedResourceChangesForCard($sozialesCard)->guthabenChange)->toEqual(new MoneyAmount(-500));

        // activating the card costs the modified price
        $guthabenBefore = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->players[0]);
        $this->handle(new StartSpielzug($this->players[0]));
        $this->handle(ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        expect(PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->players[0]))
            ->toEqual($guthabenBefore->subtract(new MoneyAmount(1500)));

        // the next Konjunkturphase has the normal costs again
        $this->setKonjunkturphaseDefinition($normalKonjunkturphase);
        $this->startNewKonjunkturphaseWithCardsOnTop([$bildungCard, $sozialesCard]);
        $calculator = AktionsCalculator::forStream($this->getGameEvents());
        expect($calculator->getModifiedResourceChangesForCard($bildungCard)->guthabenChange)->toEqual(new MoneyAmount(-1000))
            ->and($calculator->getModifiedResourceChangesForCard($sozialesCard)->guthabenChange)->toEqual(new MoneyAmount(-1000));
    });
});
