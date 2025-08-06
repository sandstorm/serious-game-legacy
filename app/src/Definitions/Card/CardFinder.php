<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\Dto\AnswerOption;
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
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\AnswerId;
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
            "j1" => new JobCardDefinition(
                id: new CardId('j1'),
                title: 'freiwilliges Praktikum',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+12000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 0,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j2" => new JobCardDefinition(
                id: new CardId('j2'),
                title: 'Studium',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+14000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 0,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j3" => new JobCardDefinition(
                id: new CardId('j3'),
                title: 'Duale Ausbildung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+15000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 0,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j4" => new JobCardDefinition(
                id: new CardId('j4'),
                title: 'Fachkraft für Fischerei',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+16000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j5" => new JobCardDefinition(
                id: new CardId('j5'),
                title: 'Küchenhilfspersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+16500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j6" => new JobCardDefinition(
                id: new CardId('j6'),
                title: 'Barpersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+17900),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j7" => new JobCardDefinition(
                id: new CardId('j7'),
                title: 'Mitarbeitende im Call-Center',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+17500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j8" => new JobCardDefinition(
                id: new CardId('j8'),
                title: 'Entsorgungsfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+17000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j9" => new JobCardDefinition(
                id: new CardId('j9'),
                title: 'Fahrpersonal Taxi',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+18700),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j10" => new JobCardDefinition(
                id: new CardId('j10'),
                title: 'Gebäudereinigungsfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+18200),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j11" => new JobCardDefinition(
                id: new CardId('j11'),
                title: 'Fachkraft für Hauswirtschaft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+18400),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j12" => new JobCardDefinition(
                id: new CardId('j12'),
                title: 'Zustellpersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+19000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j13" => new JobCardDefinition(
                id: new CardId('j13'),
                title: 'Friseurfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+21800),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j14" => new JobCardDefinition(
                id: new CardId('j14'),
                title: 'Fachkraft für Malerei',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+22000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j15" => new JobCardDefinition(
                id: new CardId('j15'),
                title: 'Fachkraft für Lackierung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+22000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j16" => new JobCardDefinition(
                id: new CardId('j16'),
                title: 'Medizinisches Assistenzpersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+22500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j17" => new JobCardDefinition(
                id: new CardId('j17'),
                title: 'Hotelfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+22800),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j18" => new JobCardDefinition(
                id: new CardId('j18'),
                title: 'Fachkraft Kosmetik',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                gehalt: new MoneyAmount(+23000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j19" => new JobCardDefinition(
                id: new CardId('j19'),
                title: 'Zahnmedizinisches Fachpersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+23500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j20" => new JobCardDefinition(
                id: new CardId('j20'),
                title: 'Fachkraft für Fliesen-, Platten und Mosaikarbeiten',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+24000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j21" => new JobCardDefinition(
                id: new CardId('j21'),
                title: 'Pflegefachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j22" => new JobCardDefinition(
                id: new CardId('j22'),
                title: 'Verkaufspersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+25500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j23" => new JobCardDefinition(
                id: new CardId('j23'),
                title: 'Verwaltungsfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+25000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j24" => new JobCardDefinition(
                id: new CardId('j24'),
                title: 'Verkaufsfachkraft im KfZ-Bereich',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+26000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j25" => new JobCardDefinition(
                id: new CardId('j25'),
                title: 'Industriekauffrau/-mann',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+26500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j26" => new JobCardDefinition(
                id: new CardId('j26'),
                title: 'Einzelhandelsfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+26100),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j27" => new JobCardDefinition(
                id: new CardId('j27'),
                title: 'Buchhandelsfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+27000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j28" => new JobCardDefinition(
                id: new CardId('j28'),
                title: 'Fachkraft für Immobilienwirtschaft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+27500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j29" => new JobCardDefinition(
                id: new CardId('j29'),
                title: 'Person im Fahrdienst',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+28000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j30" => new JobCardDefinition(
                id: new CardId('j30'),
                title: 'Fachkraft im Bäckerhandwerk',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+28200),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j31" => new JobCardDefinition(
                id: new CardId('j31'),
                title: 'Forstmanagement',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+28800),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j32" => new JobCardDefinition(
                id: new CardId('j32'),
                title: 'Kaufmännische Fachkraft im Büromanagenent ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+28500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j33" => new JobCardDefinition(
                id: new CardId('j33'),
                title: 'Kfz-Mechatronikfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+28300),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j34" => new JobCardDefinition(
                id: new CardId('j34'),
                title: 'Zahntechnische Fachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+29000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j35" => new JobCardDefinition(
                id: new CardId('j35'),
                title: 'Empfangspersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+29500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j36" => new JobCardDefinition(
                id: new CardId('j36'),
                title: 'Mechatronikfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+30000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j37" => new JobCardDefinition(
                id: new CardId('j37'),
                title: 'Pädagogische Fachkraft im Bereich Kindererziehung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+30000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j38" => new JobCardDefinition(
                id: new CardId('j38'),
                title: 'Leitung der Küche',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+30500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j39" => new JobCardDefinition(
                id: new CardId('j39'),
                title: 'Meisterin im Schreinerhandwerk',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+32000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j40" => new JobCardDefinition(
                id: new CardId('j40'),
                title: 'Fachkraft für Umwelttechnologie',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+32000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j41" => new JobCardDefinition(
                id: new CardId('j41'),
                title: 'Assistenz Geschäftsleitung ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+33500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j42" => new JobCardDefinition(
                id: new CardId('j42'),
                title: 'Fachkraft für soziale Arbeit',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+33000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j43" => new JobCardDefinition(
                id: new CardId('j43'),
                title: 'Empfangspersonal',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+33100),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j44" => new JobCardDefinition(
                id: new CardId('j44'),
                title: 'Fachkraft für Logistik',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j45" => new JobCardDefinition(
                id: new CardId('j45'),
                title: 'Fachkraft Elektronik',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j46" => new JobCardDefinition(
                id: new CardId('j46'),
                title: 'Teamleitung NGO',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+35000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j47" => new JobCardDefinition(
                id: new CardId('j47'),
                title: 'Fachkraft im Garten- und Landschaftsbau',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+34000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j48" => new JobCardDefinition(
                id: new CardId('j48'),
                title: 'IT-Fachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+36500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j49" => new JobCardDefinition(
                id: new CardId('j49'),
                title: 'Promotion',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+26000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j50" => new JobCardDefinition(
                id: new CardId('j50'),
                title: 'Speditionskauffrau/-mann',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+32000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j51" => new JobCardDefinition(
                id: new CardId('j51'),
                title: 'Fachkraft für soziale Arbeit',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+33000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j52" => new JobCardDefinition(
                id: new CardId('j52'),
                title: 'Meisterin im Bäckerhandwerk',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+34200),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j53" => new JobCardDefinition(
                id: new CardId('j53'),
                title: 'Offizierslaufbahn',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+34500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j54" => new JobCardDefinition(
                id: new CardId('j54'),
                title: 'Fachkraft für Logistik',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+36000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j55" => new JobCardDefinition(
                id: new CardId('j55'),
                title: 'Bankfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+38000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j56" => new JobCardDefinition(
                id: new CardId('j56'),
                title: 'Psychologin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+34200),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j57" => new JobCardDefinition(
                id: new CardId('j57'),
                title: 'Key Account Management',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+46000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j58" => new JobCardDefinition(
                id: new CardId('j58'),
                title: 'Veranstaltungsmanagement',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+48500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j59" => new JobCardDefinition(
                id: new CardId('j59'),
                title: 'Finanzfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+48000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j60" => new JobCardDefinition(
                id: new CardId('j60'),
                title: 'Leitung von Spitzengastronomie',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+49500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j61" => new JobCardDefinition(
                id: new CardId('j61'),
                title: 'Management Vertriebsingenieur',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+49000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j62" => new JobCardDefinition(
                id: new CardId('j62'),
                title: 'Hochschuldozierende',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+49800),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j63" => new JobCardDefinition(
                id: new CardId('j63'),
                title: 'Oberstudienrätin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+50000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j64" => new JobCardDefinition(
                id: new CardId('j64'),
                title: 'Fachkraft für Finanzanalysen',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+54000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j65" => new JobCardDefinition(
                id: new CardId('j65'),
                title: 'Software Engineer',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+55000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j66" => new JobCardDefinition(
                id: new CardId('j66'),
                title: 'Archäologische Fachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+59000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j67" => new JobCardDefinition(
                id: new CardId('j67'),
                title: 'Fachkraft für Wirtschaftsprüfung ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+60000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 2,
                ),
            ),
            "j68" => new JobCardDefinition(
                id: new CardId('j68'),
                title: 'Schulleitung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+61000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j69" => new JobCardDefinition(
                id: new CardId('j69'),
                title: 'Fachärztin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+63000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j70" => new JobCardDefinition(
                id: new CardId('j70'),
                title: 'Veranstaltungsmanagement',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+62000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j71" => new JobCardDefinition(
                id: new CardId('j71'),
                title: 'IT-Teamleitung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+65000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j72" => new JobCardDefinition(
                id: new CardId('j72'),
                title: 'Unternehmensberatung ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+65000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j73" => new JobCardDefinition(
                id: new CardId('j73'),
                title: 'Notarin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+70000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j74" => new JobCardDefinition(
                id: new CardId('j74'),
                title: 'Speditions- und Logistikfachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+42000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j75" => new JobCardDefinition(
                id: new CardId('j75'),
                title: 'Habilitation',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+45000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j76" => new JobCardDefinition(
                id: new CardId('j76'),
                title: 'Logistikmanagement',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+55000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j77" => new JobCardDefinition(
                id: new CardId('j77'),
                title: 'Archäologische Fachkraft',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+62000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j78" => new JobCardDefinition(
                id: new CardId('j78'),
                title: 'Klinikprofessorin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+64000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j79" => new JobCardDefinition(
                id: new CardId('j79'),
                title: 'Steuerberatung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+64000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j80" => new JobCardDefinition(
                id: new CardId('j80'),
                title: 'Tierärztin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+65000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j81" => new JobCardDefinition(
                id: new CardId('j81'),
                title: 'Psychologin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+65000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j82" => new JobCardDefinition(
                id: new CardId('j82'),
                title: 'Führungskraft Marketing',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+70000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 2,
                ),
            ),
            "j83" => new JobCardDefinition(
                id: new CardId('j83'),
                title: 'Schulleitung ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+75000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j84" => new JobCardDefinition(
                id: new CardId('j84'),
                title: 'Technische Leitung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+78000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j85" => new JobCardDefinition(
                id: new CardId('j85'),
                title: 'Führungskraft Personalwesen',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+80000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j86" => new JobCardDefinition(
                id: new CardId('j86'),
                title: 'Hochschuldozierende',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+82000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 2,
                ),
            ),
            "j87" => new JobCardDefinition(
                id: new CardId('j87'),
                title: 'Leitung des Bildungsministeriums',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+85000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j88" => new JobCardDefinition(
                id: new CardId('j88'),
                title: 'Software Engineer',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+90000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j89" => new JobCardDefinition(
                id: new CardId('j89'),
                title: 'Leitung Finanzbuchhaltung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+95000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j90" => new JobCardDefinition(
                id: new CardId('j90'),
                title: 'Notarin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+92000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j91" => new JobCardDefinition(
                id: new CardId('j91'),
                title: 'Raumfahrtpersonal ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+96000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j92" => new JobCardDefinition(
                id: new CardId('j92'),
                title: 'Offizierslaufbahn',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+93000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j93" => new JobCardDefinition(
                id: new CardId('j93'),
                title: 'Unternehmensberatung ',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+95000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j94" => new JobCardDefinition(
                id: new CardId('j94'),
                title: 'IT-Teamleitung',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+97000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j95" => new JobCardDefinition(
                id: new CardId('j95'),
                title: 'Piloten-Crew',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+100000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j96" => new JobCardDefinition(
                id: new CardId('j96'),
                title: 'Profibasketballerin',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+120000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 2,
                ),
            ),
            "j97" => new JobCardDefinition(
                id: new CardId('j97'),
                title: 'CEO (Geschäftsführung)',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+120000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j98" => new JobCardDefinition(
                id: new CardId('j98'),
                title: 'Fußballprofi',
                description: 'Wenn Du einen Job hast, kannst pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+130000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 2,
                ),
            ),
            "mj1" => new MinijobCardDefinition(
                id: new CardId('mj1'),
                title: 'Aushilfe Gastronomie',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
            ),
            "mj2" => new MinijobCardDefinition(
                id: new CardId('mj2'),
                title: 'Reinigungskraft',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+4000),
                ),
            ),
            "mj3" => new MinijobCardDefinition(
                id: new CardId('mj3'),
                title: 'Jugendbetreuung',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+1000),
                ),
            ),
            "mj4" => new MinijobCardDefinition(
                id: new CardId('mj4'),
                title: 'Aushilfe Bäckerei',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3000),
                ),
            ),
            "mj5" => new MinijobCardDefinition(
                id: new CardId('mj5'),
                title: 'Stadtführungen ',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3500),
                ),
            ),
            "mj6" => new MinijobCardDefinition(
                id: new CardId('mj6'),
                title: 'Aushilfe Fitnesstudio',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
            ),
            "mj7" => new MinijobCardDefinition(
                id: new CardId('mj7'),
                title: 'Ferienjob bei Automobilhersteller',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+4000),
                ),
            ),
            "mj8" => new MinijobCardDefinition(
                id: new CardId('mj8'),
                title: 'Studentische Hilfskraft',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3500),
                ),
            ),
            "mj9" => new MinijobCardDefinition(
                id: new CardId('mj9'),
                title: 'Nachhilfe ',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2000),
                ),
            ),
            "mj10" => new MinijobCardDefinition(
                id: new CardId('mj10'),
                title: 'Hausaufgabenbetreuung',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2100),
                ),
            ),
            "mj11" => new MinijobCardDefinition(
                id: new CardId('mj11'),
                title: 'Aushilfe Supermarkt',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5500),
                ),
            ),
            "mj12" => new MinijobCardDefinition(
                id: new CardId('mj12'),
                title: 'Haushaltshilfe ',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2800),
                ),
            ),
            "mj13" => new MinijobCardDefinition(
                id: new CardId('mj13'),
                title: 'Babysitten',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+1000),
                ),
            ),
            "mj14" => new MinijobCardDefinition(
                id: new CardId('mj14'),
                title: 'Aushilfe Wochenmarkt',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3000),
                ),
            ),
            "mj15" => new MinijobCardDefinition(
                id: new CardId('mj15'),
                title: 'Aushilfe Ernte',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2900),
                ),
            ),
            "mj16" => new MinijobCardDefinition(
                id: new CardId('mj16'),
                title: 'Aushilfe Messestand',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5000),
                ),
            ),
            "mj17" => new MinijobCardDefinition(
                id: new CardId('mj17'),
                title: 'Pflegen von Gemeinschaftsgärten',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2500),
                ),
            ),
            "mj18" => new MinijobCardDefinition(
                id: new CardId('mj18'),
                title: 'Aushilfe Unverpacktladen',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3100),
                ),
            ),
            "mj19" => new MinijobCardDefinition(
                id: new CardId('mj19'),
                title: 'Reparieren von Fahrrädern in einer Werkstatt',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+1500),
                ),
            ),
            "mj20" => new MinijobCardDefinition(
                id: new CardId('mj20'),
                title: 'Auslieferung von Zeitung',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+2200),
                ),
            ),
            "mj21" => new MinijobCardDefinition(
                id: new CardId('mj21'),
                title: 'Aushilfe Second-Hand-Laden',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3500),
                ),
            ),
            "mj22" => new MinijobCardDefinition(
                id: new CardId('mj22'),
                title: 'Verkauf von selbstgemachten Produkten',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+800),
                ),
            ),
            "mj23" => new MinijobCardDefinition(
                id: new CardId('mj23'),
                title: 'Aushilfe Paketversand',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+5100),
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
            "e4" => new EreignisCardDefinition(
                id: new CardId('e4'),
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
            "e5" => new EreignisCardDefinition(
                id: new CardId('e5'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kind',
                description: 'Deine Tochter Alisa wird geboren. Glückwunsch! Du zahlst von nun an regelmäßig 10 % deines Einkommens (mind. 1.000 €) für alle anfallenden Kosten und einmalig 2.000 € für die Erstaustattung. Wegen des neu gewonnenen Netzwerks (Babyschwimmen usw.) erhälst du aber auch zwei Sozialpunkte.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [ModifierId::LEBENSHALTUNGSKOSTEN_MULTIPLIER, ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE],
                modifierParameters: new ModifierParameters(modifyLebenshaltungskostenMultiplier: 0.1, modifyLebenshaltungskostenMinValue: new MoneyAmount(+1000)),
            ),
            "wb0" => new WeiterbildungCardDefinition(
                id: new CardId('wb0'),
                title: 'Quiz – Protektionismus',
                description: 'Nicht immer stimmen die Interessen von Arbeitgeberinnen und Arbeitnehmerinnnen überein. Welches der folgenden Interessen ist KEIN typisches Interesse von Arbeitnehmerinnen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Tarifliche Entlohnung und Arbeitsplatzsicherheit", true),
                    new AnswerOption(new AnswerId("b"), "Angemessene Vergütung und soziale Absicherung"),
                    new AnswerOption(new AnswerId("c"), "Maximale Kosteneffizienz und unternehmerische Flexibilität"),
                    new AnswerOption(new AnswerId("d"), "Karriereförderung und Mitbestimmungsmöglichkeiten"),
                ],
            ),
            "wb1" => new WeiterbildungCardDefinition(
                id: new CardId('wb1'),
                title: 'Quiz – Arbeitnehmerinteressen',
                description: 'Nicht immer stimmen die Interessen von Arbeitgeberinnen und Arbeitnehmerinnnen überein. Welches der folgenden Interessen ist KEIN typisches Interesse von Arbeitnehmerinnen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Tarifliche Entlohnung und Arbeitsplatzsicherheit", true),
                    new AnswerOption(new AnswerId("b"), "Angemessene Vergütung und soziale Absicherung"),
                    new AnswerOption(new AnswerId("c"), "Maximale Kosteneffizienz und unternehmerische Flexibilität"),
                    new AnswerOption(new AnswerId("d"), "Karriereförderung und Mitbestimmungsmöglichkeiten"),
                ],
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
