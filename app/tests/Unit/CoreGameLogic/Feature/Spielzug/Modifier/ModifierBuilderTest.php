<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierBuilder;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

@covers(ModifierBuilder::class);

beforeEach(function () {
});

describe('build', function () {

    /**
     * Make sure all modifiers can be build. This is actually more a test for the importer to
     * prevent a mismatch between modifierIds and modifierParameters. A modifier that cannot be built would crash
     * every game in which the card/Konjunkturphase is used (also after the game has ended, e.g. for the export).
     */
    it('can build all modifiers for all EreignisCards in CardFinder (including legacy cards)', function () {
        foreach (CardFinder::getInstance()->getAllCardsIncludingLegacyForTesting() as $card) {
            if (!$card instanceof EreignisCardDefinition) {
                continue;
            }
            foreach ($card->getModifierIds() as $modifierId) {
                ModifierBuilder::build(
                    modifierId: $modifierId,
                    playerId: PlayerId::fromString("testplayer"),
                    playerTurn: new PlayerTurn(1),
                    year: new Year(1),
                    modifierParameters: $card->getModifierParameters(),
                    description: "for testing",
                );
            }
        }
    })->throwsNoExceptions();

    it('can build all modifiers for all Konjunkturphasen', function () {
        foreach (KonjunkturphaseFinder::getAllKonjunkturphasen() as $konjunkturphase) {
            foreach ($konjunkturphase->getModifierIds() as $modifierId) {
                ModifierBuilder::build(
                    modifierId: $modifierId,
                    playerId: PlayerId::fromString("testplayer"),
                    playerTurn: new PlayerTurn(1),
                    year: new Year(1),
                    modifierParameters: $konjunkturphase->getModifierParameters(),
                    description: "for testing",
                );
            }
        }
    })->throwsNoExceptions();

    it('only references existing cards as requiredCardId', function () {
        $allCards = CardFinder::getInstance()->getAllCardsIncludingLegacyForTesting();
        foreach ($allCards as $card) {
            if ($card instanceof EreignisCardDefinition && $card->getRequiredCardId() !== null) {
                expect(array_key_exists($card->getRequiredCardId()->value, $allCards))
                    ->toBeTrue('Card ' . $card->getId()->value . ' requires the unknown card ' . $card->getRequiredCardId()->value);
            }
        }
    });

});
