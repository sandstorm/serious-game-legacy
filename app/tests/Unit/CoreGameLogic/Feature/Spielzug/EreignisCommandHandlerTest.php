<?php
declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\Dto\Zeitslots;
use Domain\Definitions\Konjunkturphase\Dto\ZeitslotsPerPlayer;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
    $this->konjunkturphaseDefinition = new KonjunkturphaseDefinition(
        id: KonjunkturphasenId::create(1),
        type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
        name: 'Erste Erholung',
        description: 'Nachdem eine globale Krise die internationalen Lieferketten stark gestört hatte, ist der Konsum jedoch noch verhalten, da Haushalte und Unternehmen vorsichtig agieren. Unternehmen beginnen, ihre Lager aufzufüllen und Neueinstellungen zu tätigen. Die Zentralbank hält den Leitzins daher mit 1 % niedrig, um günstige Kredite zu ermöglichen und Investitionen sowie Konsumausgaben zu begünstigen. Dadurch bleiben Kredite günstig und die Unternehmen sowie Haushalte können leichter investieren und konsumieren.',
        additionalEvents: '',
        zeitsteine: new Zeitsteine(
            [
                new ZeitsteinePerPlayer(2, 6),
                new ZeitsteinePerPlayer(3, 5),
                new ZeitsteinePerPlayer(4, 5),
            ]
        ),
        kompetenzbereiche: [
            new KompetenzbereichDefinition(
                name: CategoryId::BILDUNG_UND_KARRIERE,
                zeitslots: new Zeitslots([
                    new ZeitslotsPerPlayer(2, 3),
                    new ZeitslotsPerPlayer(3, 2),
                    new ZeitslotsPerPlayer(4, 2),
                ])
            ),
            new KompetenzbereichDefinition(
                name: CategoryId::SOZIALES_UND_FREIZEIT,
                zeitslots: new Zeitslots([
                    new ZeitslotsPerPlayer(2, 4),
                    new ZeitslotsPerPlayer(3, 5),
                    new ZeitslotsPerPlayer(4, 5),
                ])
            ),
            new KompetenzbereichDefinition(
                name: CategoryId::INVESTITIONEN,
                zeitslots: new Zeitslots([
                    new ZeitslotsPerPlayer(2, 4),
                    new ZeitslotsPerPlayer(3, 5),
                    new ZeitslotsPerPlayer(4, 5),
                ])
            ),
            new KompetenzbereichDefinition(
                name: CategoryId::JOBS,
                zeitslots: new Zeitslots([
                    new ZeitslotsPerPlayer(2, 3),
                    new ZeitslotsPerPlayer(3, 4),
                    new ZeitslotsPerPlayer(4, 4),
                ])
            ),
        ],
        modifierIds: [
            ModifierId::FOR_TESTING_ONLY_ALWAYS_TRIGGER_EREIGNIS,
        ],
        modifierParameters: new ModifierParameters(),
        auswirkungen: [
            new AuswirkungDefinition(
                scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                value: 4
            ),
            new AuswirkungDefinition(
                scope: AuswirkungScopeEnum::STOCKS_BONUS,
                value: 0
            ),
            new AuswirkungDefinition(
                scope: AuswirkungScopeEnum::DIVIDEND,
                value: 1.40
            ),
            new AuswirkungDefinition(
                scope: AuswirkungScopeEnum::REAL_ESTATE,
                value: 0
            ),
            new AuswirkungDefinition(
                scope: AuswirkungScopeEnum::CRYPTO,
                value: 4
            ),
        ]
    );
    KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
        $this->konjunkturphaseDefinition
    ]);
});

describe('Ereignisse', function () {
    it('will apply resourceChanges correctly', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('cardToTest1'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    zeitsteineChange: +1,
                    bildungKompetenzsteinChange: +1,
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToTriggerEvent'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                resourceChanges: new ResourceChanges(),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(PlayerState::getGuthabenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0])->value)->toEqual(Configuration::STARTKAPITAL_VALUE + 1000)
            ->and(PlayerState::getZeitsteineForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0]))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(1)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(1)
            ->and(PlayerState::getGuthabenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1])->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
            ->and(PlayerState::getZeitsteineForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1]))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[1]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis,
                $this->getPlayers()[1]))->toEqual(0);
    });

    it('will deduct 50% of any profit if the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('cardToTest1'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
                    zeitsteineChange: +1,
                    bildungKompetenzsteinChange: +2,
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToTriggerEvent'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                resourceChanges: new ResourceChanges(),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(PlayerState::getGuthabenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0])->value)->toEqual(5000)
            ->and(PlayerState::getZeitsteineForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0]))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getGuthabenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1])->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
            ->and(PlayerState::getZeitsteineForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1]))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[1]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis,
                $this->getPlayers()[1]))->toEqual(0);
    });

    it('will not modify negative resourceChanges if the player is insolvent', function () {
        /** @var TestCase $this */
        $this->setupInsolvenz();

        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('cardToTest1'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    zeitsteineChange: +1,
                    bildungKompetenzsteinChange: +2,
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToTriggerEvent'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(2000),
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(PlayerState::getGuthabenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0])->value)->toEqual(1000)
            ->and(PlayerState::getZeitsteineForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0]))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getGuthabenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1])->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
            ->and(PlayerState::getZeitsteineForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1]))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[1]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis,
                $this->getPlayers()[1]))->toEqual(0);
    });
});

describe('Childbirth', function () {
    it('will calculate the correct lebenshaltungskosten after childbirth without a job', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('e150'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Tristan ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage: 10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToTriggerEvent'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'for testing',
                description: 'for testing',
                resourceChanges: new ResourceChanges(),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::SOZIALES_UND_FREIZEIT));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0])->value)
            ->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE + 1000)
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1])->value)
            ->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });

    it('will calculate the correct lebenshaltungskosten after childbirth with a job', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('e150'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Tristan ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage: 10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToTriggerEvent'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'for testing',
                description: 'for testing',
                resourceChanges: new ResourceChanges(),
            ),
            new JobCardDefinition(
                id: new CardId('jobForTest'),
                title: 'offered 1',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(100000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];

        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);
        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('jobForTest')));
        $this->handle(new EndSpielzug($this->getPlayers()[0]));

        $this->handle(DoMinijob::create($this->getPlayers()[1]));
        $this->handle(new EndSpielzug($this->getPlayers()[1]));

        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::SOZIALES_UND_FREIZEIT));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[0])->value)
            ->toEqual(100000 * (Configuration::LEBENSHALTUNGSKOSTEN_PERCENT + 10) / 100)
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEventsAfterEreignis,
                $this->getPlayers()[1])->value)
            ->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });
});
