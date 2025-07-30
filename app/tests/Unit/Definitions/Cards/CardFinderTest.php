<?php
declare(strict_types=1);

namespace Tests\Definitions\Cards;

use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\Pile;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

beforeEach(function () {

});

describe('overrideCardsForTesting', function () {
    it('adds the correct cards', function () {
        $cardsToUse = [
            "t00" => new KategorieCardDefinition(
                id: new CardId('t00'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung zur Meisterin',
                description: 'Du entscheidest dich eine berufbegleitende Weiterbildung zur Meisterin zu machen. Die Weiterbildung erstreckt sich über 8 Monate. In dieser Zeit reduzierst du deine Arbeit auf 70 %. Solltest du bereits einen Job haben, so erhälst du 30 % weniger Gehalt. Wenn du noch keinen Job hast, so kostet es dich 8.000 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                ),
            ),
            "t01" => new KategorieCardDefinition(
                id: new CardId('t01'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t02" => new KategorieCardDefinition(
                id: new CardId('t02'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
        ];

        CardFinder::getInstance()->overrideCardsForTesting($cardsToUse);
        $actualCard = CardFinder::getInstance()->getCardById(new CardId('t02'), KategorieCardDefinition::class);
        expect($actualCard)->not->toBeNull()
            ->and($actualCard->getTitle())->toBe('Spende');
    });
});

describe('getCardById', function () {

    it('returns the correct card', function () {
        $cardId = new CardId('t00');
        $cardsToUse = [
            "t00" => new KategorieCardDefinition(
                id: $cardId,
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung zur Meisterin',
                description: 'Du entscheidest dich eine berufbegleitende Weiterbildung zur Meisterin zu machen. Die Weiterbildung erstreckt sich über 8 Monate. In dieser Zeit reduzierst du deine Arbeit auf 70 %. Solltest du bereits einen Job haben, so erhälst du 30 % weniger Gehalt. Wenn du noch keinen Job hast, so kostet es dich 8.000 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                ),
            ),
        ];

        CardFinder::getInstance()->overrideCardsForTesting($cardsToUse);

        $actualCard = CardFinder::getInstance()->getCardById($cardId, KategorieCardDefinition::class);
        expect($actualCard->getId())->toEqual($cardId);
    });

    it('throws exception when the card does not exist', function () {
        $cardIdThatDoesNotExist = new CardId('doesnotexist');
        $cardId = new CardId('cardThatExists');

        $cardsToUse = [
            "t00" => new KategorieCardDefinition(
                id: $cardId,
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung zur Meisterin',
                description: 'Du entscheidest dich eine berufbegleitende Weiterbildung zur Meisterin zu machen. Die Weiterbildung erstreckt sich über 8 Monate. In dieser Zeit reduzierst du deine Arbeit auf 70 %. Solltest du bereits einen Job haben, so erhälst du 30 % weniger Gehalt. Wenn du noch keinen Job hast, so kostet es dich 8.000 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                ),
            ),
        ];

        CardFinder::getInstance()->overrideCardsForTesting($cardsToUse);
        $actualCard = CardFinder::getInstance()->getCardById($cardIdThatDoesNotExist);
    })->throws(\RuntimeException::class, 'Card [CardId: doesnotexist] does not exist', 1747645954);

});

describe('generatePilesFromCards', function () {
    it('adds each card to the correct pile', function () {
        $cardsToUse = [
            "t00" => new KategorieCardDefinition(
                id: new CardId('t00'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung zur Meisterin',
                description: 'Du entscheidest dich eine berufbegleitende Weiterbildung zur Meisterin zu machen. Die Weiterbildung erstreckt sich über 8 Monate. In dieser Zeit reduzierst du deine Arbeit auf 70 %. Solltest du bereits einen Job haben, so erhälst du 30 % weniger Gehalt. Wenn du noch keinen Job hast, so kostet es dich 8.000 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                //TODO: Folgen -30% Gehalt einmalig (Option)
                ),
            ),
            "t01" => new KategorieCardDefinition(
                id: new CardId('t01'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t02" => new KategorieCardDefinition(
                id: new CardId('t02'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t03" => new KategorieCardDefinition(
                id: new CardId('t03'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Große Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 500 €.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t04" => new KategorieCardDefinition(
                id: new CardId('t04'),
                categoryId: CategoryId::MINIJOBS,
                title: 'testjob',
                description: 'for testing',
                phaseId: LebenszielPhaseId::ANY_PHASE,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2000),
                ),
            ),
        ];

        CardFinder::getInstance()->overrideCardsForTesting($cardsToUse);
        $actualPiles = CardFinder::getInstance()->generatePilesFromCards();
        $expectedPiles = [
            new Pile(
                new PileId(CategoryId::BILDUNG_UND_KARRIERE, LebenszielPhaseId::PHASE_1),
                [new CardId("t00")]
            ),
            new Pile(
                new PileId(CategoryId::SOZIALES_UND_FREIZEIT, LebenszielPhaseId::PHASE_1),
                [new CardId("t01"), new CardId("t02")]
            ),
            new Pile(
                new PileId(CategoryId::SOZIALES_UND_FREIZEIT, LebenszielPhaseId::PHASE_2),
                [new CardId("t03")]
            ),
            new Pile(
                new PileId(CategoryId::MINIJOBS, LebenszielPhaseId::ANY_PHASE),
                [new CardId("t04")]
            ),
        ];
        expect($actualPiles)->toContainEqual(...$expectedPiles);
    });

    it('respects year constraints', function () {
        $cardsToUse = [
            "t00" => new KategorieCardDefinition(
                id: new CardId('t00'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung zur Meisterin',
                description: 'Du entscheidest dich eine berufbegleitende Weiterbildung zur Meisterin zu machen. Die Weiterbildung erstreckt sich über 8 Monate. In dieser Zeit reduzierst du deine Arbeit auf 70 %. Solltest du bereits einen Job haben, so erhälst du 30 % weniger Gehalt. Wenn du noch keinen Job hast, so kostet es dich 8.000 €.',
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                //TODO: Folgen -30% Gehalt einmalig (Option)
                ),
            ),
            "t01" => new KategorieCardDefinition(
                id: new CardId('t01'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t02" => new KategorieCardDefinition(
                id: new CardId('t02'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t03" => new KategorieCardDefinition(
                id: new CardId('t03'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Große Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 500 €.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "t04" => new KategorieCardDefinition(
                id: new CardId('t04'),
                categoryId: CategoryId::MINIJOBS,
                title: 'testjob',
                description: 'for testing',
                phaseId: LebenszielPhaseId::ANY_PHASE,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2000),
                ),
            ),
        ];

        CardFinder::getInstance()->overrideCardsForTesting($cardsToUse);
        $actualPiles = CardFinder::getInstance()->generatePilesFromCards(new Year(1));
        $expectedPiles = [
            new Pile(
                new PileId(CategoryId::SOZIALES_UND_FREIZEIT, LebenszielPhaseId::PHASE_1),
                [new CardId("t01")]
            ),
            new Pile(
                new PileId(CategoryId::SOZIALES_UND_FREIZEIT, LebenszielPhaseId::PHASE_2),
                [new CardId("t03")]
            ),
            new Pile(
                new PileId(CategoryId::MINIJOBS, LebenszielPhaseId::ANY_PHASE),
                [new CardId("t04")]
            ),
        ];
        expect($actualPiles)->toContainEqual(...$expectedPiles)
            ->and(count($actualPiles))->toBe(3);
    });
});
