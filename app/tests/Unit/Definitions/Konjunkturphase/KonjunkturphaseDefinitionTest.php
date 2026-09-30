<?php

declare(strict_types=1);

namespace Tests\Definitions\Konjunkturphase;

use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
use Domain\Definitions\Konjunkturphase\Dto\DisplayedAuswirkung;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

/**
 * @param ModifierId[] $modifierIds
 * @param AuswirkungDefinition[] $auswirkungen
 * @param ConditionalResourceChange[] $conditionalResourceChanges
 */
function createKonjunkturphaseDefinitionForDisplayTest(
    array $modifierIds = [],
    ModifierParameters $modifierParameters = new ModifierParameters(),
    array $auswirkungen = [],
    array $conditionalResourceChanges = [],
    string $zeitsteineDescription = '',
): KonjunkturphaseDefinition {
    return new KonjunkturphaseDefinition(
        id: KonjunkturphasenId::create(1),
        type: KonjunkturphaseTypeEnum::BOOM,
        name: 'Test',
        description: 'Test description',
        additionalEvents: '',
        zeitsteine: new Zeitsteine([new ZeitsteinePerPlayer(2, 6)]),
        kompetenzbereiche: [],
        modifierIds: $modifierIds,
        modifierParameters: $modifierParameters,
        auswirkungen: $auswirkungen,
        conditionalResourceChanges: $conditionalResourceChanges,
        zeitsteineDescription: $zeitsteineDescription,
    );
}

describe('getDisplayedAuswirkungen', function () {
    it('uses 100 % for Gehalt and Lebenshaltungskosten if they are not modified', function () {
        expect(createKonjunkturphaseDefinitionForDisplayTest()->getDisplayedAuswirkungen())->toEqual([
            new DisplayedAuswirkung(label: 'Gehalt', value: 100, unit: '%', isLowerBetter: false),
            new DisplayedAuswirkung(label: 'Lebenshaltungskosten', value: 100, unit: '%', isLowerBetter: true),
            new DisplayedAuswirkung(label: 'Kreditzins', value: 0, unit: '%', isLowerBetter: true),
            new DisplayedAuswirkung(label: 'Dividende', value: 0, unit: ' €', isLowerBetter: false),
        ]);
    });

    it('shows the modified values and hides the Kursbonus of Aktien, Crypto and Immobilien', function () {
        $konjunkturphase = createKonjunkturphaseDefinitionForDisplayTest(
            modifierIds: [ModifierId::GEHALT_CHANGE, ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER],
            modifierParameters: new ModifierParameters(modifyGehaltPercent: 90, modifyLebenshaltungskostenMultiplier: 105),
            auswirkungen: [
                new AuswirkungDefinition(scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE, value: 5.5),
                new AuswirkungDefinition(scope: AuswirkungScopeEnum::STOCKS_BONUS, value: 8),
                new AuswirkungDefinition(scope: AuswirkungScopeEnum::CRYPTO, value: 20),
                new AuswirkungDefinition(scope: AuswirkungScopeEnum::DIVIDEND, value: 1.5),
                new AuswirkungDefinition(scope: AuswirkungScopeEnum::REAL_ESTATE, value: 4),
            ],
        );

        expect(array_map(fn (DisplayedAuswirkung $auswirkung) => [$auswirkung->label, $auswirkung->value], $konjunkturphase->getDisplayedAuswirkungen()))
            ->toEqual([['Gehalt', 90], ['Lebenshaltungskosten', 105], ['Kreditzins', 5.5], ['Dividende', 1.5]]);
    });
});

describe('getDisplayedAuswirkungDescriptions', function () {
    it('returns the additional modifiers, the conditional ResourceChanges and the Zeitsteine', function () {
        $konjunkturphase = createKonjunkturphaseDefinitionForDisplayTest(
            modifierIds: [
                ModifierId::GEHALT_CHANGE, // already shown in the Auswirkungen
                ModifierId::BILDUNG_UND_KARRIERE_COST, // hidden
                ModifierId::SOZIALES_UND_FREIZEIT_COST, // hidden
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER, // already shown in the Auswirkungen
                ModifierId::KREDITSPERRE,
            ],
            modifierParameters: new ModifierParameters(
                modifyGehaltPercent: 90,
                modifyKostenBildungUndKarrierePercent: 95,
                modifyKostenSozialesUndFreizeitPercent: 95,
                modifyLebenshaltungskostenMultiplier: 90,
            ),
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_LOAN,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-200)),
                    isExtraZins: true,
                    description: 'Einmaliger Extrazins für alle mit Darlehen i.H.v. 200 €',
                ),
                new ConditionalResourceChange( // without description -> not shown
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(500)),
                ),
            ],
            zeitsteineDescription: '-1 Zeitstein für alle',
        );

        expect($konjunkturphase->getDisplayedAuswirkungDescriptions())->toBe([
            'Kreditsperre',
            'Einmaliger Extrazins für alle mit Darlehen i.H.v. 200 €',
            '-1 Zeitstein für alle',
        ]);
    });

    it('returns no descriptions if there are no other Auswirkungen', function () {
        expect(createKonjunkturphaseDefinitionForDisplayTest()->getDisplayedAuswirkungDescriptions())->toBe([]);
    });
});
