<?php

declare(strict_types=1);

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Event\BerufsunfaehigkeitsversicherungWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
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
use Tests\DomainTestCase;

beforeEach(function () {
    /** @var DomainTestCase $this */
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
        /** @var DomainTestCase $this */
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

        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            )->value)->toEqual(Configuration::STARTKAPITAL_VALUE + 1000)
            ->and(PlayerState::getZeitsteineForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            ))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(1)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(1)
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            )->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
            ->and(PlayerState::getZeitsteineForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            ))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[1]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            ))->toEqual(0);
    });

    it('will deduct 50% of any profit if the player is insolvent', function () {
        /** @var DomainTestCase $this */
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

        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            )->value)->toEqual(5000)
            ->and(PlayerState::getZeitsteineForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            ))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            )->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
            ->and(PlayerState::getZeitsteineForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            ))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[1]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            ))->toEqual(0);
    });

    it('will not modify negative resourceChanges if the player is insolvent', function () {
        /** @var DomainTestCase $this */
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

        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            )->value)->toEqual(1000)
            ->and(PlayerState::getZeitsteineForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            ))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[0]))->toEqual(2)
            ->and(PlayerState::getGuthabenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            )->value)->toEqual(Configuration::STARTKAPITAL_VALUE)
            ->and(PlayerState::getZeitsteineForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            ))->toEqual($this->getKonjunkturphaseDefinition()->zeitsteine->getAmountOfZeitsteineForPlayer(2))
            ->and(PlayerState::getBildungsKompetenzsteine($gameEventsAfterEreignis, $this->getPlayers()[1]))->toEqual(0)
            ->and(PlayerState::getFreizeitKompetenzsteine(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            ))->toEqual(0);
    });
});

describe('Childbirth', function () {
    it('will calculate the correct lebenshaltungskosten after childbirth without a job', function () {
        /** @var DomainTestCase $this */
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

        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::SOZIALES_UND_FREIZEIT));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            )->value)
            ->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE + 1000)
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            )->value)
            ->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });

    it('will calculate the correct lebenshaltungskosten after childbirth with a job', function () {
        /** @var DomainTestCase $this */
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
        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(AcceptJobOffer::create($this->getPlayers()[0], new CardId('jobForTest')));
        $this->handle(new EndSpielzug($this->getPlayers()[0]));

        $this->handle(new StartSpielzug($this->getPlayers()[1]));
        $this->handle(DoMinijob::create($this->getPlayers()[1]));
        $this->handle(new EndSpielzug($this->getPlayers()[1]));

        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::SOZIALES_UND_FREIZEIT));
        $gameEventsAfterEreignis = $this->getGameEvents();
        expect($gameEventsAfterEreignis->findLast(EreignisWasTriggered::class))->not()->toBeNull()
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[0]
            )->value)
            ->toEqual(100000 * (Configuration::LEBENSHALTUNGSKOSTEN_PERCENT + 10) / 100)
            ->and(MoneySheetState::calculateLebenshaltungskostenForPlayer(
                $gameEventsAfterEreignis,
                $this->getPlayers()[1]
            )->value)
            ->toEqual(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE);
    });
});

describe('Ereignis Modifier application', function () {
    it('applies Investitionssperre modifier and prevents buying investments', function () {
        /** @var DomainTestCase $this */
        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('cardWithInvestitionssperre'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Investitionssperre',
                description: 'Du darfst diese Konjunkturphase nicht investieren.',
                year: new Year(1),
                resourceChanges: new ResourceChanges(),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
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

        // activate card, which triggers the Ereignis with Investitionssperre
        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEvents = $this->getGameEvents();
        expect($gameEvents->findLast(EreignisWasTriggered::class))->not()->toBeNull();

        // end turn for player 0
        $this->handle(new EndSpielzug($this->getPlayers()[0]));

        // player 1 does mini job and ends turn
        $this->handle(new StartSpielzug($this->getPlayers()[1]));
        $this->handle(DoMinijob::create($this->getPlayers()[1]));
        $this->handle(new EndSpielzug($this->getPlayers()[1]));

        // player 0 tries to buy investments - should be blocked by Investitionssperre
        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(\Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer::create(
            $this->getPlayers()[0],
            \Domain\Definitions\Investments\ValueObject\InvestmentId::MERFEDES_PENZ,
            10
        ));
    })->throws(\RuntimeException::class, 'Du darfst diese Konjunkturphase keine Investitionen tätigen.', 1752066529);

    it('applies Kreditsperre modifier and prevents taking out a loan', function () {
        /** @var DomainTestCase $this */
        $cardsForTesting = [
            new EreignisCardDefinition(
                id: new CardId('cardWithKreditsperre'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kreditsperre',
                description: 'Du darfst diese Konjunkturphase keinen Kredit aufnehmen.',
                year: new Year(1),
                resourceChanges: new ResourceChanges(),
                modifierIds: [
                    ModifierId::KREDITSPERRE,
                ],
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

        // activate card, which triggers the Ereignis with Kreditsperre
        $this->handle(new StartSpielzug($this->getPlayers()[0]));
        $this->handle(ActivateCard::create($this->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));
        $gameEvents = $this->getGameEvents();
        expect($gameEvents->findLast(EreignisWasTriggered::class))->not()->toBeNull();

        // end turn for player 0
        $this->handle(new EndSpielzug($this->getPlayers()[0]));

        // player 1 does mini job and ends turn
        $this->handle(new StartSpielzug($this->getPlayers()[1]));
        $this->handle(DoMinijob::create($this->getPlayers()[1]));
        $this->handle(new EndSpielzug($this->getPlayers()[1]));

        // player 0 tries to take out a loan - should be blocked by Kreditsperre
        $this->handle(\Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer::create(
            $this->getPlayers()[0],
            5000
        ));
    })->throws(\RuntimeException::class, 'Du darfst diese Konjunkturphase keine Kredite aufnehmen.');
});

describe('Berufsunfähigkeit', function () {
    /**
     * @return CardDefinition[]
     */
    function cardsForBerufsunfaehigkeit(): array
    {
        return [
            new EreignisCardDefinition(
                id: new CardId('e44'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Eine chronische Sehnenscheidenentzündung zwingt dich, deinen Beruf aufzugeben. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(),
                ereignisRequirementIds: [],
                gewichtung: 1,
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToTriggerEvent'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: 'for testing',
                resourceChanges: new ResourceChanges(),
            ),
            new JobCardDefinition(
                id: new CardId('jobForTest'),
                title: 'offered 1',
                description: 'for testing',
                gehalt: new MoneyAmount(100000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                ),
            ),
        ];
    }

    /**
     * Player 0 accepts a job (and optionally concludes the Berufsunfähigkeitsversicherung), then draws the
     * Berufsunfähigkeit Ereignis in their next Spielzug.
     */
    function drawBerufsunfaehigkeitEreignis(DomainTestCase $testCase, bool $withBerufsunfaehigkeitsversicherung): void
    {
        $testCase->startNewKonjunkturphaseWithCardsOnTop(cardsForBerufsunfaehigkeit());
        $testCase->handle(new StartSpielzug($testCase->getPlayers()[0]));
        $testCase->handle(AcceptJobOffer::create($testCase->getPlayers()[0], new CardId('jobForTest')));
        if ($withBerufsunfaehigkeitsversicherung) {
            $testCase->handle(ConcludeInsuranceForPlayer::create($testCase->getPlayers()[0], InsuranceId::create(3)));
        }
        $testCase->handle(new EndSpielzug($testCase->getPlayers()[0]));

        $testCase->handle(new StartSpielzug($testCase->getPlayers()[1]));
        $testCase->handle(DoMinijob::create($testCase->getPlayers()[1]));
        $testCase->handle(new EndSpielzug($testCase->getPlayers()[1]));

        $testCase->handle(new StartSpielzug($testCase->getPlayers()[0]));
        $testCase->handle(ActivateCard::create($testCase->getPlayers()[0], CategoryId::BILDUNG_UND_KARRIERE));

        expect($testCase->getGameEvents()->findLast(EreignisWasTriggered::class)->ereignisCardId->value)->toBe('e44');
    }

    it('keeps paying the Gehalt until the end of the year if the player has a Berufsunfähigkeitsversicherung', function () {
        /** @var DomainTestCase $this */
        drawBerufsunfaehigkeitEreignis($this, withBerufsunfaehigkeitsversicherung: true);

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->getPlayers()[0]))->toBeNull()
            ->and($gameEvents->findLastOrNull(BerufsunfaehigkeitsversicherungWasActivated::class))->not->toBeNull()
            ->and(PlayerState::getCurrentGehaltForPlayer($gameEvents, $this->getPlayers()[0])->value)->toEqual(100000);

        // the Lohnfortzahlung ends with the year (the cards must still exist to load the events referencing them)
        $this->startNewKonjunkturphaseWithCardsOnTop(cardsForBerufsunfaehigkeit());
        expect(PlayerState::getCurrentGehaltForPlayer($this->getGameEvents(), $this->getPlayers()[0])->value)->toEqual(0);
    });

    it('stops paying the Gehalt immediately if the player has no Berufsunfähigkeitsversicherung', function () {
        /** @var DomainTestCase $this */
        drawBerufsunfaehigkeitEreignis($this, withBerufsunfaehigkeitsversicherung: false);

        $gameEvents = $this->getGameEvents();
        expect(PlayerState::getJobForPlayer($gameEvents, $this->getPlayers()[0]))->toBeNull()
            ->and($gameEvents->findLastOrNull(BerufsunfaehigkeitsversicherungWasActivated::class))->toBeNull()
            ->and(PlayerState::getCurrentGehaltForPlayer($gameEvents, $this->getPlayers()[0])->value)->toEqual(0);
    });
});

/**
 * Lets player 0 activate a Soziales & Freizeit card, which triggers the given Ereignis (the Konjunkturphase of this
 * test always triggers an Ereignis). Optionally concludes an insurance before.
 */
function drawEreignisForFirstPlayer(DomainTestCase $testCase, EreignisCardDefinition $ereignisCard, ?InsuranceId $insuranceId = null): GameEvents
{
    $testCase->startNewKonjunkturphaseWithCardsOnTop([
        $ereignisCard,
        new KategorieCardDefinition(
            id: new CardId('cardToTriggerEreignis'),
            categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
            title: 'for testing',
            description: 'for testing',
            resourceChanges: new ResourceChanges(),
        ),
    ]);
    $player = $testCase->getPlayers()[0];
    $testCase->handle(new StartSpielzug($player));
    if ($insuranceId !== null) {
        $testCase->handle(ConcludeInsuranceForPlayer::create($player, $insuranceId));
    }
    $testCase->handle(ActivateCard::create($player, CategoryId::SOZIALES_UND_FREIZEIT));

    return $testCase->getGameEvents();
}

/**
 * @param ModifierId[] $modifierIds
 * @param EreignisPrerequisitesId[] $ereignisRequirementIds
 */
function ereignisCardForSozialesUndFreizeit(
    ResourceChanges $resourceChanges = new ResourceChanges(),
    array $modifierIds = [],
    array $ereignisRequirementIds = [],
): EreignisCardDefinition {
    return new EreignisCardDefinition(
        id: new CardId('ereignisForTesting'),
        categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
        title: 'for testing',
        description: 'for testing',
        phaseId: LebenszielPhaseId::PHASE_1,
        year: new Year(1),
        resourceChanges: $resourceChanges,
        modifierIds: $modifierIds,
        modifierParameters: new ModifierParameters(),
        ereignisRequirementIds: $ereignisRequirementIds,
        gewichtung: 1,
    );
}

describe('Ereignisse covered by insurances', function () {
    it('pays out the Invaliditätsleistung of an accident only with a private Unfallversicherung', function (bool $withUnfallversicherung, int $expectedGuthabenChange) {
        /** @var DomainTestCase $this */
        $gameEvents = drawEreignisForFirstPlayer(
            $this,
            ereignisCardForSozialesUndFreizeit(
                resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(6000)),
                modifierIds: [ModifierId::PRIVATE_UNFALLVERSICHERUNG],
            ),
            $withUnfallversicherung ? InsuranceId::create(2) : null,
        );

        expect($gameEvents->findLast(EreignisWasTriggered::class)->getResourceChanges($this->players[0])->guthabenChange->value)
            ->toEqual($expectedGuthabenChange);
    })->with([
        'without Unfallversicherung' => [false, 0],
        'with Unfallversicherung' => [true, 6000],
    ]);

    it('covers the damage only with a Haftpflichtversicherung', function (bool $withHaftpflichtversicherung, int $expectedGuthabenChange) {
        /** @var DomainTestCase $this */
        $gameEvents = drawEreignisForFirstPlayer(
            $this,
            ereignisCardForSozialesUndFreizeit(
                resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1200)),
                modifierIds: [ModifierId::HAFTPFLICHTVERSICHERUNG],
            ),
            $withHaftpflichtversicherung ? InsuranceId::create(1) : null,
        );

        expect($gameEvents->findLast(EreignisWasTriggered::class)->getResourceChanges($this->players[0])->guthabenChange->value)
            ->toEqual($expectedGuthabenChange);
    })->with([
        'without Haftpflichtversicherung' => [false, -1200],
        'with Haftpflichtversicherung' => [true, 0],
    ]);
});

/**
 * Each test only offers an Ereignis that the player must not draw. If the Ereignis was drawn anyway (instead of failing
 * because no Ereignis matches), the filter is broken. Two cards would not work: the random pick would hide a broken
 * filter in 50% of the runs.
 */
describe('Ereignisse the player must not draw', function () {
    it('does not draw an Ereignis that requires a job if the player has no job', function () {
        /** @var DomainTestCase $this */
        drawEreignisForFirstPlayer($this, ereignisCardForSozialesUndFreizeit(
            ereignisRequirementIds: [EreignisPrerequisitesId::HAS_JOB],
        ));
    })->throws(\RuntimeException::class, 'No EreignisCard matches the current requirements', 1753874959);

    it('does not draw an Ereignis with a job loss if the player has no job, even without the HAS_JOB requirement', function (ModifierId $modifierId) {
        /** @var DomainTestCase $this */
        drawEreignisForFirstPlayer($this, ereignisCardForSozialesUndFreizeit(
            modifierIds: [$modifierId],
        ));
    })->with([
        ModifierId::JOBVERLUST,
        ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
    ])->throws(\RuntimeException::class, 'No EreignisCard matches the current requirements', 1753874959);

    it('does not draw an Ereignis the player cannot afford', function () {
        /** @var DomainTestCase $this */
        drawEreignisForFirstPlayer($this, ereignisCardForSozialesUndFreizeit(
            resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1000000)),
        ));
    })->throws(\RuntimeException::class, 'No EreignisCard matches the current requirements', 1753874959);
});
