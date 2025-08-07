<?php
declare(strict_types=1);

namespace Tests;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\InsuranceDefinition;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;
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
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ForCoreGameLogic $coreGameLogic;
    protected GameId $gameId;
    /**
     * @var PlayerId[]
     */
    protected array $players;

    /**
     * @var InsuranceDefinition[]
     */
    protected array $insurances;

    /**
     * @var KonjunkturphaseDefinition|null
     */
    protected ?KonjunkturphaseDefinition $konjunkturphaseDefinition;

    private function generatePlayerIds(int $numberOfPlayers)
    {
        assert(2 <= $numberOfPlayers && $numberOfPlayers <= 4, "Only 2-4 players are supported");
        $playerIds = [];
        for ($i = 0; $i < $numberOfPlayers; $i++) {
            $playerIds[$i] = PlayerId::fromString('p' . $i + 1);
        }
        return $playerIds;
    }

    /**
     * @param int $numberOfPlayers
     * @param CardDefinition[]|null $cards
     * @return void
     */
    public function setupBasicGameWithoutKonjunkturphase(int $numberOfPlayers = 2, ?array $cards = null): void
    {
        $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
        $this->gameId = GameId::fromString('game1');
        $this->players = $this->generatePlayerIds($numberOfPlayers);
        CardFinder::getInstance()->overrideCardsForTesting(
            $cards !== null
            ? [...$cards, ...$this->getCardsForEreignisse()] // Add at least one ereignisCard for each category to avoid errors
            : [
                ...$this->getCardsForBildungAndKarriere(),
                ...$this->getCardsForSozialesAndFreizeit(),
                ...$this->getCardsForJobs(),
                ...$this->getCardsForMinijobs(),
                ...$this->getCardsForEreignisse(),
                ...$this->getCardsForWeiterbildung(),
            ]
        );

        InsuranceFinder::getInstance()->overrideInsurancesForTesting([
            new InsuranceDefinition(
                id: InsuranceId::create(1),
                type: InsuranceTypeEnum::HAFTPFLICHT,
                description: 'Haftpflichtversicherung',
                annualCost: [
                    1 => new MoneyAmount(100),
                    2 => new MoneyAmount(120),
                    3 => new MoneyAmount(140),
                ]
            ),
            new InsuranceDefinition(
                id: InsuranceId::create(2),
                type: InsuranceTypeEnum::UNFALLVERSICHERUNG,
                description: 'Unfallversicherung',
                annualCost: [
                    1 => new MoneyAmount(150),
                    2 => new MoneyAmount(180),
                    3 => new MoneyAmount(200),
                ]
            ),
            new InsuranceDefinition(
                id: InsuranceId::create(3),
                type: InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                description: 'Berufsunfähigkeitsversicherung',
                annualCost: [
                    1 => new MoneyAmount(500),
                    2 => new MoneyAmount(600),
                    3 => new MoneyAmount(700),
                ]
            ),
        ]);

        $this->insurances = InsuranceFinder::getInstance()->getAllInsurances();

        $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
            numberOfPlayers: $numberOfPlayers,
        )->withFixedPlayerIdsForTesting(...$this->players));
        foreach ($this->players as $index => $player) {
            $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
                playerId: $player,
                name: 'Player ' . $index,
            ));
            $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
                playerId: $player,
                lebenszielId: LebenszielId::create($index % 2 + 1),
            ));
        }

        $this->coreGameLogic->handle($this->gameId, StartGame::create());
    }

    public function setupBasicGame(int $numberOfPlayers = 2, ?array $cards = null): void
    {
        $this->setupBasicGameWithoutKonjunkturphase($numberOfPlayers, $cards);

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
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::ZEITSTEINE,
                    modifier: 1,
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LEBENSERHALTUNGSKOSTEN,
                    modifier: 100
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BILDUNG,
                    modifier: 100
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::FREIZEIT,
                    modifier: 100
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.40
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    modifier: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BONUS_INCOME,
                    modifier: 0
                ),
            ]
        );
        KonjunkturphaseFinder::getInstance()->overrideKonjunkturphaseDefinitionsForTesting([
            $this->konjunkturphaseDefinition
        ]);
        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
                ->withFixedKonjunkturphaseForTesting($this->konjunkturphaseDefinition)
                ->withFixedCardOrderForTesting()
        );
    }

    /**
     * Simulate a complete game round for all players.
     * Only works if the cards used consume all the Zeitsteine!
     *
     * @return void
     */
    public function makeSpielzugForPlayersByPlayingCard(): void
    {
        foreach ($this->players as $player) {
            $this->coreGameLogic->handle($this->gameId, ActivateCard::create($player, CategoryId::BILDUNG_UND_KARRIERE));
            $this->coreGameLogic->handle($this->gameId, new EndSpielzug($player));
        }

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        foreach ($this->players as $player) {
            $this->coreGameLogic->handle(
                $this->gameId,
                EnterLebenshaltungskostenForPlayer::create($player,
                    MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $player))
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                EnterSteuernUndAbgabenForPlayer::create($player,
                    MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $player))
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                CompleteMoneysheetForPlayer::create($player)
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                MarkPlayerAsReadyForKonjunkturphaseChange::create($player)
            );
        }
    }

    /**
     * @return void
     */
    public function makeSpielzugForPlayersBySkippingACard(): void
    {
        foreach ($this->players as $player) {
            $this->coreGameLogic->handle($this->gameId, new SkipCard($player, CategoryId::BILDUNG_UND_KARRIERE));
            $this->coreGameLogic->handle($this->gameId, new EndSpielzug($player));
        }

        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        foreach ($this->players as $player) {
            $this->coreGameLogic->handle(
                $this->gameId,
                EnterLebenshaltungskostenForPlayer::create($player,
                    MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $player))
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                EnterSteuernUndAbgabenForPlayer::create($player,
                    MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $player))
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                CompleteMoneysheetForPlayer::create($player)
            );
            $this->coreGameLogic->handle(
                $this->gameId,
                MarkPlayerAsReadyForKonjunkturphaseChange::create($player)
            );
        }
    }

    /**
     * @return JobCardDefinition[]
     */
    protected function getCardsForJobs(): array
    {
        return [
            "j100" => new JobCardDefinition(
                id: new CardId('j100'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
        ];
    }

    /**
     * @return KategorieCardDefinition[]
     */
    protected function getCardsForSozialesAndFreizeit(): array
    {
        return [
            "suf0" => new KategorieCardDefinition(
                id: new CardId('suf0'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf1" => new KategorieCardDefinition(
                id: new CardId('suf1'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf2" => new KategorieCardDefinition(
                id: new CardId('suf2'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'kostenlose Nachhilfe',
                description: 'Du gibst kostenlose Nachhilfe für sozial benachteiligte Kinder. Du verlierst einen Zeitstein.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
        ];
    }

    /**
     * @return KategorieCardDefinition[]
     */
    protected function getCardsForBildungAndKarriere(): array
    {
        return [
            "buk0" => new KategorieCardDefinition(
                id: new CardId('buk0'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachkurs',
                description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk1" => new KategorieCardDefinition(
                id: new CardId('buk1'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Erste-Hilfe-Kurs',
                description: 'Du machst einen Erste-Hilfe-Kurs, um im Notfall richtig zu reagieren.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk2" => new KategorieCardDefinition(
                id: new CardId('buk2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Gedächtnistraining',
                description: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
        ];
    }

    /**
     * @return MinijobCardDefinition[]
     */
    protected function getCardsForMinijobs(): array
    {
        return [
            "mj0" => new MinijobCardDefinition(
                id: new CardId('mj0'),
                title: 'Minijob',
                description: 'Kellnerin im Ausland. Einmalzahlung 5.000 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
            ),
            "mj1" => new MinijobCardDefinition(
                id: new CardId('mj1'),
                title: 'Minijob',
                description: 'Putzkraft im Ausland. Einmalzahlung 2.000 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2000),
                ),
            ),

        ];
    }

    /**
     * @return EreignisCardDefinition[]
     */
    protected function getCardsForEreignisse(): array
    {
        return [
            "e0" => new EreignisCardDefinition(
                id: new CardId('e0'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Nichts',
                description: 'Es passiert nichts, damit die Tests vorhersehbar bleiben',
                year: new Year(1),
                resourceChanges: new ResourceChanges(),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
            "e1" => new EreignisCardDefinition(
                id: new CardId('e1'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Nichts',
                description: 'Es passiert nichts, damit die Tests vorhersehbar bleiben',
                year: new Year(1),
                resourceChanges: new ResourceChanges(),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
        ];
    }

    /**
     * @return WeiterbildungCardDefinition[]
     */
    protected function getCardsForWeiterbildung(): array
    {
        return [
            "wb0" => new WeiterbildungCardDefinition(
                id: new CardId('wb0'),
                description: 'Ich mache eine Weiterbildung. Warum machst du die Weiterbildung?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Tarifliche Entlohnung und Arbeitsplatzsicherheit", true),
                    new AnswerOption(new AnswerId("b"), "Angemessene Vergütung und soziale Absicherung"),
                    new AnswerOption(new AnswerId("c"), "Maximale Kosteneffizienz und unternehmerische Flexibilität"),
                    new AnswerOption(new AnswerId("d"), "Karriereförderung und Mitbestimmungsmöglichkeiten"),
                ],
            ),
            "wb1" => new WeiterbildungCardDefinition(
                id: new CardId('wb1'),
                description: 'Ich mache eine Weiterbildung. Warum machst du die Weiterbildung?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "sygyrsgsfgydrg", true),
                    new AnswerOption(new AnswerId("b"), "hydrgdyrgydrgrydsgysgrygys"),
                    new AnswerOption(new AnswerId("c"), "hhhhhhhhhhhhhhh"),
                    new AnswerOption(new AnswerId("d"), "mmmmmmmmmmm"),
                ],
            ),

        ];
    }

    public function startNewKonjunkturphaseWithCardsOnTop(array $cardsForTesting): void
    {
        /**
         * @var CardDefinition[] $allCards
         */
        $allCards = [
            ...$cardsForTesting, // Add cards to start of the list -> will be drawn first
            ...$this->getCardsForBildungAndKarriere(),
            ...$this->getCardsForSozialesAndFreizeit(),
            ...$this->getCardsForJobs(),
            ...$this->getCardsForMinijobs(),
            ...$this->getCardsForEreignisse(),
            ...$this->getCardsForWeiterbildung(),
        ];
        $allCardsWithIdsAsKey = [];
        foreach ($allCards as $card) {
            $allCardsWithIdsAsKey[$card->getId()->value] = $card;
        }
        CardFinder::getInstance()->overrideCardsForTesting($allCardsWithIdsAsKey);

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()
                ->withFixedKonjunkturphaseForTesting($this->konjunkturphaseDefinition)
                ->withFixedCardOrderForTesting()
        );
    }
}
