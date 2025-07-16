<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\PileId;
use Random\Randomizer;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
final class CardFinder
{
    /**
     * @var array<PileID::value, CardDefinition[]> $cards
     */
    private array $cards;

    private static ?self $instance = null;

    /**
     * @param array<PileID::value, CardDefinition[]> $cards
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
     * @param array<PileID::value, CardDefinition[]> $cards
     * @return void
     */
    public function overrideCardsForTesting(array $cards): void
    {
        self::getInstance()->cards = $cards;
    }

    private static function initialize(): self
    {
        self::$instance = new self([
            PileId::BILDUNG_PHASE_1->value => [
                "buk0" => new KategorieCardDefinition(
                    id: new CardId('buk0'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Sprachkurs',
                    description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-11000),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),
                "buk1" => new KategorieCardDefinition(
                    id: new CardId('buk1'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Erste-Hilfe-Kurs',
                    description: 'Du machst einen Erste-Hilfe-Kurs, um im Notfall richtig zu reagieren.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-300),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),
                "buk2" => new KategorieCardDefinition(
                    id: new CardId('buk2'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Gedächtnistraining',
                    description: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        bildungKompetenzsteinChange: +1,
                    ),
                ),

                //TODO: Card is duplicate!
                "buk3" => new KategorieCardDefinition(
                    id: new CardId('buk3'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Irgendwas',
                    description: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        bildungKompetenzsteinChange: +1,
                    ),
                ),

//                "buk4" => new KategorieCardDefinition(
//                    id: new CardId('buk4'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Teilnahme Coaching-Seminaren',
//                    description: 'Glückwunsch! Die Teilnahme an Coaching-Seminaren zahlen sich gut aus. Du gewinnst bei
//                    einem Wettbewerb für junge, ambitionierte Führungskräfte den ersten Platz und bekommst einen Sprecherpart bei einem großen Businessevent in Frankfurt + 5.000 € Finanzspritze für dein erstes Start-Up.',
//                    resourceChanges: new ResourceChanges(
//                        guthabenChange: new MoneyAmount(+5000),
//                        //TODO: Folgen: 5000 Euro Finanzspritze
//                    ),
//                ),

                "buk5" => new KategorieCardDefinition(
                    id: new CardId('buk5'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Ausbildung zur SkilehrerIn',
                    description: 'Erfülle dir deinen Traum und mache eine Ausbildung zur SkilehrerIn. Neben technischen Wissen eignest du dir geografische und pädagogische Kenntnisse an.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-7000),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),

                "buk6" => new KategorieCardDefinition(
                    id: new CardId('buk6'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Nachhilfe',
                    description: 'Nehme dir Nachhilfe, um deine Noten zu verbessern.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-600),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),

//                "buk7" => new KategorieCardDefinition(
//                    id: new CardId('buk7'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Neue Liebe',
//                    description: 'Du bist ganz verliebt und vernachlässigst deine (Lern-)Verpflichtungen. Alles wieder aufzuholen kostet viel Zeit. Du verlierst einen Zeitstein.',
//                    resourceChanges: new ResourceChanges(
//                        zeitsteineChange: -1,
//                    ),
//                ),

//                "buk8" => new KategorieCardDefinition(
//                    id: new CardId('buk8'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Neue Wohnung',
//                    description: 'Du ziehst um und aufgrund des Umzugstress vernachlässigst du deine anderen Verpflichtungen. Dies führt zum Verlust eines Zeitsteines.',
//                    resourceChanges: new ResourceChanges(
//                        zeitsteineChange: -1,
//                    ),
//                ),

//                "buk9" => new KategorieCardDefinition(
//                    id: new CardId('buk9'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Stress',
//                    description: 'Du kannst mit dem Druck nicht umgehen und schläfst nicht genug. Du nimmst dir einen Auszeit, was einen Zeitstein kostet. ',
//                    resourceChanges: new ResourceChanges(
//                        zeitsteineChange: -1,
//                    ),
//                ),

//                "buk10" => new KategorieCardDefinition(
//                    id: new CardId('buk10'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Beförderung',
//                    description: 'Du wirst befördert und dein Gehalt steigert sich um 20 % für dieses Jahr. Solltest du die Beförderung annehmen, erhöht sich allerdings auch deinen Arbeitszeit diese und nächste Runde (-1 Zeitstein). Du erhälst zudem einen Karrierepunkt. ',
//                    resourceChanges: new ResourceChanges(
//                        zeitsteineChange: -1,
//                        bildungKompetenzsteinChange: +1,
//                        //TODO: guthabenChange: + 20%, (Prozentangabe)
//                        //TODO: Vorraussetzung Eintritt Karte, wenn Erwerbseinkommen vorhanden
//                    ),
//                ),

//                "buk11" => new KategorieCardDefinition(
//                    id: new CardId('buk11'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Kündigung',
//                    description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust kündigst du unüberlegt',
//                    resourceChanges: new ResourceChanges(
//                    //TODO: Vorraussetzung Eintritt Karte, wenn Erwerbseinkommen vorhanden
//                    //TODO: Folgen Jobverlust
//                    ),
//                ),

                "buk12" => new KategorieCardDefinition(
                    id: new CardId('buk12'),
                    pileId: PileId::BILDUNG_PHASE_1,
                    title: 'Weiterbildung zur Meisterin',
                    description: 'Du entscheidest dich eine berufbegleitende Weiterbildung zur Meisterin zu machen. Die Weiterbildung erstreckt sich über 8 Monate. In dieser Zeit reduzierst du deine Arbeit auf 70 %. Solltest du bereits einen Job haben, so erhälst du 30 % weniger Gehalt. Wenn du noch keinen Job hast, so kostet es dich 8.000 €.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-8000),
                        bildungKompetenzsteinChange: +2,
                        //TODO: Folgen -30% Gehalt einmalig (Option)
                    ),
                ),

//                "buk13" => new KategorieCardDefinition(
//                    id: new CardId('buk13'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Jobverlust',
//                    description: 'Die wirtschaftliche Lage ist angespannt und und es kommt zu Kurzarbeit. Du erhälst nur noch 50 % deines Einkommens. Wenn du keinen Job hast, bist du nicht betroffen.',
//                    resourceChanges: new ResourceChanges(
//                    //TODO: Vorraussetzung Eintritt Karte, wenn Erwerbseinkommen vorhanden
//                    //TODO: guthabenChange: -50% deines Gehalts,
//                    ),
//                ),

//                "buk14" => new KategorieCardDefinition(
//                    id: new CardId('buk14'),
//                    pileId: PileId::BILDUNG_PHASE_1,
//                    title: 'Berufsunfähigkeitsversicherung',
//                    description: 'Du bekommst einen chronische Sehnenscheidenentzündung (Tendinitis) und kannst deinen Beruf nicht mehr ausüben. Im Falle einer abgeschlossenen Berufsunfähigkeitsversicherung verlierst du zwar deinen aktuellen Job, darfst aber eine neue Jobkarte aufnehmen unabhängig davon, ob du die Voraussetzungen erfüllst. Der finanzielle Ausfall (Kosten) entstehen für dich somit nicht. ',
//                    resourceChanges: new ResourceChanges(
//                        guthabenChange: new MoneyAmount(-20.000),
//                        //TODO: Folgen: Jobverlust in allen Fällen wenn du keine Berufsunfähigkeitsversicherung abgeschlossen hast musst du zusätzlich zum Jobverlust den finanziellen Ausfall bezahlen.
//                    ),
//                ),
            ],
            PileId::FREIZEIT_PHASE_1->value => [
                "suf0" => new KategorieCardDefinition(
                    id: new CardId('suf0'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Ehrenamtliches Engagement',
                    description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-1200),
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),
                "suf1" => new KategorieCardDefinition(
                    id: new CardId('suf1'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Spende',
                    description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-200),
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),
                "suf2" => new KategorieCardDefinition(
                    id: new CardId('suf2'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'kostenlose Nachhilfe',
                    description: 'Du gibst kostenlose Nachhilfe für sozial benachteiligte Kinder. Du verlierst einen Zeitstein.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),
                "suf3" => new KategorieCardDefinition(
                    id: new CardId('suf3'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Ehrenamtliches Engagement',
                    description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. Dies kostet dich ein Zeitstein.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),

                "suf4" => new KategorieCardDefinition(
                    id: new CardId('suf4'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Sprachtandem',
                    description: 'Bilde ein Sprachtandem mit einem Erasmus-Studierenden und lerne viel über Sprache und fremde Kulturen.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        freizeitKompetenzsteinChange: +1,
                        //TODO: Vorraussetzung Eintritt von Ereigniskarte: mit Ereigniskarte Sprachtandem verknüpfen
                    ),
                ),

//                "suf5" => new KategorieCardDefinition(
//                    id: new CardId('suf5'),
//                    pileId: PileId::FREIZEIT_PHASE_1,
//                    title: 'Job kündigen und Weltreise',
//                    description: 'Du entscheidest dich deinen Job zu kündigen und auf Reisen zu gehen, um dich neu zu orientieren. Du verlierst damit aber auch dein Einkommen. Du erhälst einen weiteren Zeitstein und einen Punkt (Sozial/Freizeit).',
//                    resourceChanges: new ResourceChanges(
//                        zeitsteineChange: -1,
//                        freizeitKompetenzsteinChange: +1,
//                        //TODO: Vorraussetzung Eintritt von Ereigniskarte: wenn Erwerbseinkommen vorhanden
//                        //TODO: Folgen: Jobverlust
//                    ),
//                ),

                "suf6" => new KategorieCardDefinition(
                    id: new CardId('suf6'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Spende',
                    description: 'Spende einmalig 10 % deines jährlichen Einkommes für einen wohltätigen Zweck. Bei keine Einkommen spende mindestens 300 €.',
                    resourceChanges: new ResourceChanges(
                        freizeitKompetenzsteinChange: +1,
                        //TODO: (-)20% deines Gehalts oder 300€
                    ),
                ),

                "suf7" => new KategorieCardDefinition(
                    id: new CardId('suf7'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Reduzierung Arbeitszeit',
                    description: 'Reduziere in deinem Job auf 50 %. Zahle dafür mit 50 % deines Gehalts oder einem Karrierepunkt. ',
                    resourceChanges: new ResourceChanges(
                        freizeitKompetenzsteinChange: +1,
                        //TODO: (-) 50 % deines Gehalt Oder (-1) Karrierepunkt
                        //TODO: (-1) Karrierepunkt (Option)
                        //TODO: Folgen: In der nächsten Runde darfst du zwei Zeitsteine auf einmal setzten.
                        //TODO: wenn Erwerbseinkommen vorhanden
                    ),
                ),

//                "suf8" => new KategorieCardDefinition(
//                    id: new CardId('suf8'),
//                    pileId: PileId::FREIZEIT_PHASE_1,
//                    title: 'Krankheit',
//                    description: 'Du erkrankst an einer hefitgen Influenza und liegst komplett flach. Nutze einen Punkt (Soziales/Freizeit), um dich zu erholen oder gib einen Zeitstein ab.',
//                    resourceChanges: new ResourceChanges(
//                        //TODO: (- 1) Zeitstein ODER(-1) Punkt (Sozial/Freizeit)
//                    ),
//                ),

//                "suf9" => new KategorieCardDefinition(
//                    id: new CardId('suf9'),
//                    pileId: PileId::FREIZEIT_PHASE_1,
//                    title: 'Burn-Out',
//                    description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Gebe einen Zeitstein ab oder nutze einen deiner Freizeitpunkte für einen Aufenthalt in einer Rehaklinik, um dich wieder zu erholen. ',
//                    resourceChanges: new ResourceChanges(
//                        //TODO: (- 1) Zeitstein ODER(-1) Punkt (Sozial/Freizeit)
//                    ),
//                ),

                "suf10" => new KategorieCardDefinition(
                    id: new CardId('suf10'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'Sozialhilfe',
                    description: 'Engagiere eine Sozialhilfe zur Pflege deiner Großeltern.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-30.000),
                        freizeitKompetenzsteinChange: +2,
                        //TODO: Folgen: In der nächsten Runde darfst du zwei Zeitsteine auf einmal setzten
                    ),
                ),

//                "suf11" => new KategorieCardDefinition(
//                    id: new CardId('suf11'),
//                    pileId: PileId::FREIZEIT_PHASE_1,
//                    title: 'Vorstandsarbeit in einem Verein',
//                    description: 'Leider lässt sich keine andere Person finden, die deinen Vorstandsposten im Tennisverein übernimmt. Daher entscheidest du dich für eine weitere Periode den Posten zu übernehmen. Dies kostet dich einen Zeitstein.',
//                    resourceChanges: new ResourceChanges(
//                        zeitsteineChange: -1,
//                        //TODO: Vorraussetzung Eintritt von Ereignisskarte: wenn Ehrenamt Vorstandsposten gegeben
//                    ),
//                ),

//                "suf12" => new KategorieCardDefinition(
//                    id: new CardId('suf12'),
//                    pileId: PileId::FREIZEIT_PHASE_1,
//                    title: 'Einsatz für Demokratie',
//                    description: 'Deine Informationsflyer für die demokratischen Werte kommen so gut an, dass du erneut Flyer in den Druck gibst. Die kosten dich nochmals 500 €.',
//                    resourceChanges: new ResourceChanges(
//                        guthabenChange: new MoneyAmount(-500),
//                        freizeitKompetenzsteinChange: +1,
//                        //TODO: Vorraussetzung Eintritt von Ereignisskarte: wenn Einsatz für Demokratie bereits erfolgt ist
//                    ),
//                ),

//                "suf13" => new KategorieCardDefinition(
//                    id: new CardId('suf13'),
//                    pileId: PileId::FREIZEIT_PHASE_1,
//                    title: 'Geburt',
//                    description: 'Dein Sohn Tristan wird geboren.Glückwunsch! Du zahlst von nun an regelmäßig 5 % deines Einkommens (mind. 1.000 €) für alle anfallenden Kosten und einmalig 2.000 € für die Erstaustattung. Wegen des neu gewonnenen Netzwerks (Babyschwimmen usw.) erhälst du aber auch zwei Punkte (Sozial/Freizeit). ',
//                    resourceChanges: new ResourceChanges(
//                        guthabenChange: new MoneyAmount(-1000), //Erstaustattung
//                        freizeitKompetenzsteinChange: +2,
//                    ),
//                ),

                "suf14" => new KategorieCardDefinition(
                    id: new CardId('suf14'),
                    pileId: PileId::FREIZEIT_PHASE_1,
                    title: 'SteuerberaterIn',
                    description: 'Dir wachsen deine Unterlagen vom letzten Jahr langsam über den Kopf. Engagiere eine:n Steuerberater:in.',
                    resourceChanges: new ResourceChanges(
                        //TODO: (-) 10% deines Gehalts oder min. 2000 €
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),

            ],
            PileId::JOBS_PHASE_1->value => [
                "j0" => new JobCardDefinition(
                    id: new CardId('j0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Fachinformatikerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j1" => new JobCardDefinition(
                    id: new CardId('j1'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Pflegefachkraft',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j2" => new JobCardDefinition(
                    id: new CardId('j2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Taxifahrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(18000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                    ),
                ),
                //TODO: Not in the list!
                "j3" => new JobCardDefinition(
                    id: new CardId('j3'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Geschichtslehrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(40000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 0,
                    ),
                ),
                //TODO: Not in the list!
                "j4" => new JobCardDefinition(
                    id: new CardId('j4'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Bruchpilot:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(4000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 0,
                    ),
                ),
                "j5" => new JobCardDefinition(
                    id: new CardId('j5'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Busfahrerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen. ',
                    gehalt: new MoneyAmount(28000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                    ),
                ),
                "j6" => new JobCardDefinition(
                    id: new CardId('j6'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Friseurin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                    ),
                ),
                "j7" => new JobCardDefinition(
                    id: new CardId('j7'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Logistikerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                        freizeitKompetenzsteine: 1,
                    ),
                ),
                "j8" => new JobCardDefinition(
                    id: new CardId('j8'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Försterin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                        freizeitKompetenzsteine: 1,
                    ),
                ),
                "j9" => new JobCardDefinition(
                    id: new CardId('j9'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Teamleitung NGO',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                        //TODO: 2 Sozialpunkte (einmalig)
                    ),
                ),
                "j10" => new JobCardDefinition(
                    id: new CardId('j10'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Gärtnerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                        freizeitKompetenzsteine: 1,
                    ),
                ),
                "j11" => new JobCardDefinition(
                    id: new CardId('j11'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Umwelttechnologin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "j12" => new JobCardDefinition(
                    id: new CardId('j12'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'freiwilliges Praktikum',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                    ),
                ),
            ],
            PileId::MINIJOBS_PHASE_1->value => [
                "mj0" => new MinijobCardDefinition(
                    id: new CardId('mj0'),
                    pileId: PileId::MINIJOBS_PHASE_1,
                    title: 'Kellnerin',
                    description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+5000),
                    ),
                ),
                "mj1" => new MinijobCardDefinition(
                    id: new CardId('mj1'),
                    pileId: PileId::MINIJOBS_PHASE_1,
                    title: 'Nachhilfelehrerin',
                    description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+2000),
                    ),
                ),
                "mj2" => new MinijobCardDefinition(
                    id: new CardId('mj2'),
                    pileId: PileId::MINIJOBS_PHASE_1,
                    title: 'Babysitterin',
                    description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+1000),
                    ),
                ),
            ],
            PileId::EREIGNISSE_BILDUNG_UND_KARRIERE_PHASE_1->value => [
                "e0" => new EreignisCardDefinition(
                    id: new CardId('e0'),
                    pileId: PileId::MINIJOBS_PHASE_1,
                    title: 'Teilnahme Coaching-Seminaren',
                    description: 'Glückwunsch! Deine Teilnahme an Coaching-Seminaren zahlt sich aus: Du gewinnst bei einem Wettbewerb für junge Führungskräfte den ersten Platz und erhältst eine Finanzspritze für dein erstes Start-up.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(+5000),
                    ),
                    modifierIds: [],
                    modifierParameters: new ModifierParameters(),
                ),
                "e1" => new EreignisCardDefinition(
                    id: new CardId('e1'),
                    pileId: PileId::MINIJOBS_PHASE_1,
                    title: 'Neue Liebe',
                    description: 'Du bist verliebt und vernachlässigst dadurch deine (Lern-)Pflichten. Alles wieder aufzuholen kostet viel Zeit.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                    ),
                    modifierIds: [],
                    modifierParameters: new ModifierParameters(),
                ),
                "e2" => new EreignisCardDefinition(
                    id: new CardId('e2'),
                    pileId: PileId::MINIJOBS_PHASE_1,
                    title: 'Beförderung',
                    description: 'Du wirst befördert – dein Gehalt erhöht sich dieses Jahr um 20%.',
                    resourceChanges: new ResourceChanges(),
                    modifierIds: [ModifierId::GEHALT_CHANGE],
                    modifierParameters: new ModifierParameters(
                        modifyGehaltPercent: 120,
                    ),
                    ereignisRequirementIds: [EreignisPrerequisitesId::JOB]
                ),
            ],
        ]);
        return self::$instance;
    }

    /**
     * @param PileId $pileId
     * @return CardDefinition[]
     */
    public function getCardsForPile(PileId $pileId): array
    {
        return match ($pileId) {
            PileId::BILDUNG_PHASE_1 => $this->getCardsForBildungAndKarriere1(),
            PileId::FREIZEIT_PHASE_1 => $this->getCardsForSozialesAndFreizeit1(),
            PileId::JOBS_PHASE_1 => $this->getCardsForJobs1(),
            PileId::MINIJOBS_PHASE_1 => $this->getCardsForMinijobs1(),
            PileId::EREIGNISSE_BILDUNG_UND_KARRIERE_PHASE_1 => $this->getCardsForEreignisseBildungUndKarriere1(),
            // TODO
            PileId::BILDUNG_PHASE_2 => [],
            PileId::FREIZEIT_PHASE_2 => [],
            PileId::JOBS_PHASE_2 => [],
            PileId::BILDUNG_PHASE_3 => [],
            PileId::FREIZEIT_PHASE_3 => [],
            PileId::JOBS_PHASE_3 => [],
        };
    }

    /**
     * @template T
     * @param CardId $cardId
     * @param class-string<T>|null $classString
     * @return T
     */
    public function getCardById(CardId $cardId, ?string $classString = CardDefinition::class): mixed
    {
        $allCards = array_reduce($this->cards, function ($cards, $currentPile) {
            return [...$cards, ...$currentPile];
        }, []);

        if (!array_key_exists($cardId->value, $allCards)) {
            throw new \RuntimeException('Card ' . $cardId . ' does not exist', 1747645954);
        }

        $card = $allCards[$cardId->value];
        if ($classString !== null && !$card instanceof $classString) {
            throw new \RuntimeException('Card ' . $cardId . ' expected to be of type ' . $classString . ' but was ' . get_class($card), 1752499517);
        }
        return $card;
    }

    /**
     * @return JobCardDefinition[]
     */
    public function getThreeRandomJobs(ResourceChanges $playerResources): array
    {
        $randomizer = new Randomizer();
        // TODO consider the player's phase
        return array_values(array_slice(
            $randomizer->shuffleArray($this->getCardsForJobs1()),
            0,
            3
        ));
    }

    /**
     * @return CardDefinition[]
     */
    private function getCardsForBildungAndKarriere1(): array
    {
        $result = $this->cards[PileId::BILDUNG_PHASE_1->value];
        foreach ($result as $item) {
            assert($item instanceof KategorieCardDefinition);
        }
        return $result;
    }

    /**
     * @return CardDefinition[]
     */
    private function getCardsForSozialesAndFreizeit1(): array
    {
        $result = $this->cards[PileId::FREIZEIT_PHASE_1->value];
        foreach ($result as $item) {
            assert($item instanceof KategorieCardDefinition);
        }
        return $result;
    }

    /**
     * @return CardDefinition[]
     */
    private function getCardsForJobs1(): array
    {
        $result = $this->cards[PileId::JOBS_PHASE_1->value];
        foreach ($result as $item) {
            assert($item instanceof JobCardDefinition);
        }
        return $result;
    }

    /**
     * @return CardDefinition[]
     */
    private function getCardsForMinijobs1(): array
    {
        $result = $this->cards[PileId::MINIJOBS_PHASE_1->value];
        foreach ($result as $item) {
            assert($item instanceof MinijobCardDefinition);
        }
        return $result;
    }

    /**
     * @return CardDefinition[]
     */
    private function getCardsForEreignisseBildungUndKarriere1(): array
    {
        $result = $this->cards[PileId::EREIGNISSE_BILDUNG_UND_KARRIERE_PHASE_1->value];
        foreach ($result as $item) {
            assert($item instanceof EreignisCardDefinition);
        }
        return $result;
    }
}
