<?php
declare(strict_types=1);


use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
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
use Tests\ComponentWithForm;
use Tests\TestCase;
use const App\Livewire\Forms\TakeOutALoanForm;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('handleStartKonjunkturphaseForPlayer', function () {
    it('throws an exception if the player has already started this Konjunkturphase', function () {
        /** @var TestCase $this */
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
    })->throws(RuntimeException::class,
        'Cannot start Konjunkturphase: Du hast diese Konjunkturphase bereits gestartet', 1751373528);

    it('works after a new Konjunkturphase has started', function () {
        /** @var TestCase $this */
        $cardsForTesting = [
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
            new KategorieCardDefinition(
                id: new CardId('cardToRemoveZeitsteine2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'for testing',
                description: '...',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1 * $this->konjunkturphaseDefinition->zeitsteine->getAmountOfZeitsteineForPlayer(2) + 1,
                ),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cardsForTesting);

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[1]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
            $this->players[0]))->toBeFalse()
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
                $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents, $this->players[0]))->toBeTrue()
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
                $this->players[1]))->toBeTrue();

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->players[0], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId,
            ActivateCard::create($this->players[1], CategoryId::BILDUNG_UND_KARRIERE));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[1]));

        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[1]));
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        expect(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
            $this->players[0]))->toBeFalse()
            ->and(KonjunkturphaseState::hasPlayerStartedCurrentKonjunkturphase($gameEvents,
                $this->players[1]))->toBeTrue();
    });

    it('applies conditional ResourceChanges correctly', function () {
        /** @var TestCase $this */
        $this->setupBasicGameWithoutKonjunkturphase();
        $konjunkturphase = new KonjunkturphaseDefinition(
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
            modifierIds: [],
            modifierParameters: new ModifierParameters(),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.40
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange( // This should be applied
                    prerequisite: EreignisPrerequisitesId::HAS_NO_CHILD,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(+2000)),
                ),
                new ConditionalResourceChange( // This should be applied
                    prerequisite: EreignisPrerequisitesId::HAS_NO_CHILD,
                    resourceChanges: new ResourceChanges(zeitsteineChange: -1, bildungKompetenzsteinChange: +2),
                ),
                new ConditionalResourceChange( // This should **not** be applied
                    prerequisite: EreignisPrerequisitesId::HAS_CHILD,
                    resourceChanges: new ResourceChanges(freizeitKompetenzsteinChange: +2),
                ),
            ]
        );
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $konjunkturphase
        ]);
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
                ->withFixedKonjunkturphaseForTesting($konjunkturphase)
                ->withFixedCardOrderForTesting()
        );

        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $initialZeitsteineForPlayer = KonjunkturphaseState::getInitialZeitsteineForCurrentKonjunkturphase($gameEvents);
        $expectedGuthaben = Configuration::STARTKAPITAL_VALUE + 2000.0;
        $actualGuthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        expect($actualGuthaben->equals($expectedGuthaben))
            ->toBeTrue("Guthaben should be $expectedGuthaben, was $actualGuthaben->value")
            ->and(PlayerState::getBildungsKompetenzsteine($gameEvents, $this->players[0]))->toEqual(2)
            ->and(PlayerState::getZeitsteineForPlayer($gameEvents, $this->players[0]))->toBe($initialZeitsteineForPlayer - 1)
            ->and(PlayerState::getFreizeitKompetenzsteine($gameEvents, $this->players[0]))->toBe(0);
    });

    it('applies Lohnsonderzahlung correctly', function () {
        /** @var TestCase $this */
        $this->setupBasicGameWithoutKonjunkturphase();

        $gehalt = 100000;
        $lohnPercent = 10;

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
            modifierIds: [],
            modifierParameters: new ModifierParameters(),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.40
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange( // This should be applied
                    prerequisite: EreignisPrerequisitesId::HAS_JOB,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(+2000)),
                    lohnsonderzahlungPercent: $lohnPercent,
                ),
            ]
        );
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition,
        ]);

        $cards = [
            new JobCardDefinition(
                id: new CardId('job1234'),
                title: 'for testing',
                description: 'easy job',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount($gehalt),
                requirements: new JobRequirements(),
            ),
        ];
        $this->startNewKonjunkturphaseWithCardsOnTop($cards);

        $this->coreGameLogic->handle($this->gameId, AcceptJobOffer::create($this->players[0], new CardId('job1234')));
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->players[0]));

        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[1]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $expectedGuthaben = Configuration::STARTKAPITAL_VALUE + $gehalt * $lohnPercent / 100;
        $actualGuthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        expect($actualGuthaben->equals($expectedGuthaben))
            ->toBeTrue("Guthaben should be $expectedGuthaben, was $actualGuthaben->value");
    });

    it('applies extraZins correctly', function () {
        /** @var TestCase $this */
        $this->setupBasicGameWithoutKonjunkturphase();

        $extraZinsAmount = new MoneyAmount(-1000);

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
            modifierIds: [],
            modifierParameters: new ModifierParameters(),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.40
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange( // This should be applied
                    prerequisite: EreignisPrerequisitesId::HAS_LOAN,
                    resourceChanges: new ResourceChanges(guthabenChange: $extraZinsAmount),
                    isExtraZins: true,
                ),
            ]
        );
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition,
        ]);
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
        );

        $takeoutLoanFormComponent = new ComponentWithForm();
        $takeoutLoanFormComponent->mount(TakeOutALoanForm::class);

        /** @var TakeOutALoanForm $takeoutLoanForm */
        $takeoutLoanForm = $takeoutLoanFormComponent->form;
        $takeoutLoanForm->loanAmount = 10000;
        $takeoutLoanForm->totalRepayment = 12500;
        $takeoutLoanForm->repaymentPerKonjunkturphase = 625;
        $takeoutLoanForm->guthaben = Configuration::STARTKAPITAL_VALUE;
        $takeoutLoanForm->zinssatz = 5;
        $takeoutLoanForm->loanId = LoanId::unique()->value;

        // player 0 takes out a loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        // player 0 takes out another loan
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->players[0],
            $takeoutLoanForm
        ));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $guthabenBeforeExtraZins = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);

        $this->coreGameLogic->handle($this->gameId, ChangeKonjunkturphase::create());
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturPhaseForPlayer::create($this->players[0]));

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        // 2 Loans -> extraZinsAmount * 2
        $expectedGuthaben = $guthabenBeforeExtraZins->add(new MoneyAmount($extraZinsAmount->value * 2));
        $actualGuthaben = PlayerState::getGuthabenForPlayer($gameEvents, $this->players[0]);
        expect($actualGuthaben->equals($expectedGuthaben))
            ->toBeTrue("Guthaben should be $expectedGuthaben, was $actualGuthaben->value");
    });
});
