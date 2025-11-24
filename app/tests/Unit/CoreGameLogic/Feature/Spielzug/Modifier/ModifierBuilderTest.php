<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierBuilder;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

@covers(ModifierBuilder::class);

beforeEach(function () {
});

describe('build', function () {

    /**
     * Make sure all modifiers can be build. This is actually more a test for the importer to
     * prevent a mismatch between modifierIds and modifierParameters.
     */
    it('can build all modifiers for all EreignisCards in CardFinder', function () {
        $allCards = [
            ...CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
                CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                LebenszielPhaseId::PHASE_1),
            ...CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
                CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                LebenszielPhaseId::PHASE_2),
            ...CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
                CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                LebenszielPhaseId::PHASE_3),
            ...CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
                CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                LebenszielPhaseId::PHASE_1),
            ...CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
                CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                LebenszielPhaseId::PHASE_2),
            ...CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
                CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                LebenszielPhaseId::PHASE_3),
        ];

        foreach ($allCards as $card) {
            $modifierIds = $card->getModifierIds();
            if (count($modifierIds) === 0) {
                continue;
            }

            $modifierParameters = $card->getModifierParameters();
            foreach ($modifierIds as $modifierId) {
                $modifier = ModifierBuilder::build(
                    modifierId: $modifierId,
                    playerId: PlayerId::fromString("testplayer"),
                    playerTurn: new PlayerTurn(1),
                    year: new Year(1),
                    modifierParameters: $modifierParameters,
                    description: "for testing",
                    );
            }

        }
    })->throwsNoExceptions();

});

