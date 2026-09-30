<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

it('renders the actions bar before the content so Weiter is visible without scrolling on small screens', function () {
    $konjunkturphase = new KonjunkturphaseDefinition(
        id: KonjunkturphasenId::create(1),
        type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
        name: 'Test',
        description: 'Test description',
        additionalEvents: '',
        zeitsteine: new Zeitsteine([new ZeitsteinePerPlayer(2, 6)]),
        kompetenzbereiche: [],
        modifierIds: [],
        modifierParameters: new ModifierParameters(),
        auswirkungen: [],
    );

    $html = view('livewire.screens.konjunkturphase-start', [
        'konjunkturphase' => $konjunkturphase,
        'previousKonjunkturphase' => null,
        'currentPage' => 0,
    ])->render();

    $actionsPos = strpos($html, 'konjunkturphase-start__actions');
    $contentPos = strpos($html, 'konjunkturphase-start__content');

    expect($actionsPos)->not->toBeFalse()
        ->and($contentPos)->not->toBeFalse()
        ->and($actionsPos)->toBeLessThan($contentPos);
});

it('shows the Auswirkungen of the Konjunkturphase, but hides the Kursbonus', function () {
    $createKonjunkturphase = fn (int $gehaltPercent, float $kreditzins) => new KonjunkturphaseDefinition(
        id: KonjunkturphasenId::create(1),
        type: KonjunkturphaseTypeEnum::BOOM,
        name: 'Test',
        description: 'Test description',
        additionalEvents: '',
        zeitsteine: new Zeitsteine([new ZeitsteinePerPlayer(2, 6)]),
        kompetenzbereiche: [],
        modifierIds: [ModifierId::GEHALT_CHANGE, ModifierId::KREDITSPERRE],
        modifierParameters: new ModifierParameters(modifyGehaltPercent: $gehaltPercent),
        auswirkungen: [
            new AuswirkungDefinition(scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE, value: $kreditzins),
            new AuswirkungDefinition(scope: AuswirkungScopeEnum::STOCKS_BONUS, value: 8),
            new AuswirkungDefinition(scope: AuswirkungScopeEnum::CRYPTO, value: 20),
            new AuswirkungDefinition(scope: AuswirkungScopeEnum::REAL_ESTATE, value: 4),
        ],
        conditionalResourceChanges: [
            new ConditionalResourceChange(
                prerequisite: EreignisPrerequisitesId::HAS_JOB,
                resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(1000)),
                description: 'Einmalige Gehaltssonderzahlung i.H.v. 1000 €',
            ),
        ],
        zeitsteineDescription: '+1 Zeitstein für alle',
    );

    $html = view('livewire.screens.konjunkturphase-start', [
        'konjunkturphase' => $createKonjunkturphase(90, 5.5),
        'previousKonjunkturphase' => $createKonjunkturphase(100, 4),
        'currentPage' => 1,
    ])->render();

    expect($html)
        ->toContain('Gehalt', '90%', 'Lebenshaltungskosten', 'Kreditzins', '5,5%', 'Dividende')
        ->toContain('Weitere Auswirkungen', 'Kreditsperre', 'Einmalige Gehaltssonderzahlung i.H.v. 1000 €', '+1 Zeitstein für alle')
        ->not->toContain('Aktien Kursbonus')
        ->not->toContain('Crypto Kursbonus')
        ->not->toContain('Immobilien');
});
