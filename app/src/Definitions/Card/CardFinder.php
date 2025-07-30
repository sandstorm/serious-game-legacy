<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\CardWithYear;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\Pile;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Random\Randomizer;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
final class CardFinder
{
    /**
     * @var CardDefinition[] $cards
     */
    private array $cards;

    private static ?self $instance = null;

    /**
     * @param CardDefinition[] $cards
     */
    private function __construct(array $cards)
    {
        $this->cards = $cards;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            return self::initialize();
        }
        return self::$instance;
    }

    public static function initializeForTesting(): void
    {
        self::initialize();
    }

    /**
     * @param CardDefinition[] $cards
     * @return void
     */
    public function overrideCardsForTesting(array $cards): void
    {
        self::getInstance()->cards = $cards;
    }

    private static function initialize(): self
    {
        self::$instance = new self([
            "buk0" => new KategorieCardDefinition(
                id: new CardId('buk0'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachkurs',
                description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
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
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
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
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),

            "buk3" => new KategorieCardDefinition(
                id: new CardId('buk3'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Irgendwas',
                description: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk5" => new KategorieCardDefinition(
                id: new CardId('buk5'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ausbildung zur SkilehrerIn',
                description: 'Erfülle dir deinen Traum und mache eine Ausbildung zur SkilehrerIn. Neben technischen Wissen eignest du dir geografische und pädagogische Kenntnisse an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk6" => new KategorieCardDefinition(
                id: new CardId('buk6'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Nachhilfe',
                description: 'Nehme dir Nachhilfe, um deine Noten zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-600),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk12" => new KategorieCardDefinition(
                id: new CardId('buk12'),
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
            "suf0" => new KategorieCardDefinition(
                id: new CardId('suf0'),
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
            "suf1" => new KategorieCardDefinition(
                id: new CardId('suf1'),
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
            "suf2" => new KategorieCardDefinition(
                id: new CardId('suf2'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'kostenlose Nachhilfe',
                description: 'Du gibst kostenlose Nachhilfe für sozial benachteiligte Kinder. Du verlierst einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf3" => new KategorieCardDefinition(
                id: new CardId('suf3'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. Dies kostet dich ein Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),

            "suf4" => new KategorieCardDefinition(
                id: new CardId('suf4'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem',
                description: 'Bilde ein Sprachtandem mit einem Erasmus-Studierenden und lerne viel über Sprache und fremde Kulturen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf6" => new KategorieCardDefinition(
                id: new CardId('suf6'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Spende einmalig 10 % deines jährlichen Einkommes für einen wohltätigen Zweck. Bei keine Einkommen spende mindestens 300 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf7" => new KategorieCardDefinition(
                id: new CardId('suf7'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Reduziere in deinem Job auf 50 %. Zahle dafür mit 50 % deines Gehalts oder einem Karrierepunkt. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf10" => new KategorieCardDefinition(
                id: new CardId('suf10'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sozialhilfe',
                description: 'Engagiere eine Sozialhilfe zur Pflege deiner Großeltern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30.000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf14" => new KategorieCardDefinition(
                id: new CardId('suf14'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'SteuerberaterIn',
                description: 'Dir wachsen deine Unterlagen vom letzten Jahr langsam über den Kopf. Engagiere eine:n Steuerberater:in.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "j0" => new JobCardDefinition(
                id: new CardId('j0'),
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "j1" => new JobCardDefinition(
                id: new CardId('j1'),
                title: 'Pflegefachkraft',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "j2" => new JobCardDefinition(
                id: new CardId('j2'),
                title: 'Taxifahrer:in',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(18000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
            "j3" => new JobCardDefinition(
                id: new CardId('j3'),
                title: 'Geschichtslehrer:in',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(40000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 0,
                ),
            ),
            "j4" => new JobCardDefinition(
                id: new CardId('j4'),
                title: 'Bruchpilot:in',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(4000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 0,
                ),
            ),
            "j5" => new JobCardDefinition(
                id: new CardId('j5'),
                title: 'Busfahrerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(28000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
            "j6" => new JobCardDefinition(
                id: new CardId('j6'),
                title: 'Friseurin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
            "j7" => new JobCardDefinition(
                id: new CardId('j7'),
                title: 'Logistikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j8" => new JobCardDefinition(
                id: new CardId('j8'),
                title: 'Försterin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j9" => new JobCardDefinition(
                id: new CardId('j9'),
                title: 'Teamleitung NGO',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "j10" => new JobCardDefinition(
                id: new CardId('j10'),
                title: 'Gärtnerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j11" => new JobCardDefinition(
                id: new CardId('j11'),
                title: 'Umwelttechnologin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                ),
            ),
            "j12" => new JobCardDefinition(
                id: new CardId('j12'),
                title: 'freiwilliges Praktikum',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
            "mj0" => new MinijobCardDefinition(
                id: new CardId('mj0'),
                title: 'Kellnerin',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
            ),
            "mj1" => new MinijobCardDefinition(
                id: new CardId('mj1'),
                title: 'Nachhilfelehrerin',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2000),
                ),
            ),
            "mj2" => new MinijobCardDefinition(
                id: new CardId('mj2'),
                title: 'Babysitterin',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+1000),
                ),
            ),
            "e0" => new EreignisCardDefinition(
                id: new CardId('e0'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Teilnahme Coaching-Seminaren',
                description: 'Glückwunsch! Deine Teilnahme an Coaching-Seminaren zahlt sich aus: Du gewinnst bei einem Wettbewerb für junge Führungskräfte den ersten Platz und erhältst eine Finanzspritze für dein erstes Start-up.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
            "e1" => new EreignisCardDefinition(
                id: new CardId('e1'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Neue Liebe',
                description: 'Du bist verliebt und vernachlässigst dadurch deine (Lern-)Pflichten. Alles wieder aufzuholen kostet viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
            "e2" => new EreignisCardDefinition(
                id: new CardId('e2'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert – dein Gehalt erhöht sich dieses Jahr um 20%.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(),
                modifierIds: [ModifierId::GEHALT_CHANGE],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent: 120,
                ),
                ereignisRequirementIds: [EreignisPrerequisitesId::JOB]
            ),
            "e3" => new EreignisCardDefinition(
                id: new CardId('e3'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deines Nachbarn stören dich sehr und es kommt zu einem Rechtsstreit. Es kommt zu Gerichtskosten von 800 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-800)),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
                ereignisRequirementIds: [EreignisPrerequisitesId::JOB]
            ),
            "e6" => new EreignisCardDefinition(
                id: new CardId('e6'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Testereignis',
                description: 'Es gibt Geld ;)',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
                modifierIds: [],
                modifierParameters: new ModifierParameters(),
            ),
        ]);
        return self::$instance;
    }

    /**
     * Returns a specific card. Provide the expected class for type safety
     *
     * @example
     * $myCard = CardFinder->getInstance()->getCardById($cardId, MinijobCardDefinition::class);
     *
     * @template T
     * @param CardId $cardId
     * @param class-string<T> $classString
     * @return T
     */
    public function getCardById(CardId $cardId, string $classString = CardDefinition::class): mixed
    {

        if (!array_key_exists($cardId->value, $this->cards)) {
            throw new \RuntimeException('Card ' . $cardId . ' does not exist', 1747645954);
        }

        $card = $this->cards[$cardId->value];
        assert($card instanceof $classString);
        if (!$card instanceof $classString) {
            throw new \RuntimeException('Card ' . $cardId . ' expected to be of type ' . $classString . ' but was ' . get_class($card),
                1752499517);
        }
        return $card;
    }

    /**
     * Returns three random jobs for the provided lebenszielPhase.
     * @return JobCardDefinition[]
     */
    public function getThreeRandomJobs(LebenszielPhaseId $lebenszielPhaseId): array
    {
        $randomizer = new Randomizer();
        return array_values(array_slice(
            $randomizer->shuffleArray($this->getCardDefinitionsByCategoryAndPhase(CategoryId::JOBS, $lebenszielPhaseId)),
            0,
            3
        ));
    }

    /**
     * Returns all cards that match the Category and Lebenszielphase. LebenszielPhaseId::ANY_PHASE is a special case
     * and matches all other phases. @see LebenszielPhaseId::looselyEquals()
     * @param CategoryId $categoryId
     * @param LebenszielPhaseId $phaseId
     * @return CardDefinition[]
     */
    public function getCardDefinitionsByCategoryAndPhase(CategoryId $categoryId, LebenszielPhaseId $phaseId): array
    {
        return array_filter($this->cards, fn($card) => $card->getCategory()->value === $categoryId->value &&
            $card->getPhase()->looselyEquals($phaseId));
    }

    /**
     * Automatically sorts all cards into piles based on their Category and Phase
     * @return Pile[] all cards sorted by pileId
     */
    public function generatePilesFromCards(Year $currentYear = new Year(3)): array
    {
        $piles = [];
        foreach ($this->cards as $card) {
            if ( // consider year constraints for phase 1 cards that have them
                $card->getPhase()->value === 1 && // is phase 1 card
                $card instanceof CardWithYear && // has year constraints
                $card->getYear()->value > $currentYear->value // year constraint not met
            ) {
                continue;
            }
            $pileId = new PileId($card->getCategory(), $card->getPhase());
            $piles[(string)$pileId][] = $card->getId();
        }
        $result = [];
        foreach ($piles as $pileId => $cards) {
            $result[] = new Pile(PileId::fromString($pileId), $cards);
        }
        return $result;
    }
}
