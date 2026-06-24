<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
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
