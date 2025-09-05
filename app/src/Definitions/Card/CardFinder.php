<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\CardWithGewichtung;
use Domain\Definitions\Card\Dto\CardWithYear;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\InvestitionenCardDefinition;
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
use Domain\Definitions\Card\ValueObject\ImmobilienType;
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
            "inv1" => new InvestitionenCardDefinition(
                id: new CardId('inv1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-75000),
                ),
                annualRent: new MoneyAmount(4125),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv2" => new InvestitionenCardDefinition(
                id: new CardId('inv2'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Studierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                ),
                annualRent: new MoneyAmount(2750),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv3" => new InvestitionenCardDefinition(
                id: new CardId('inv3'),
                title: 'Kauf Wohnung',
                description: 'Ein renoviertes Loft  mit DINKs (Double Income, No Kids) –Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(11000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv4" => new InvestitionenCardDefinition(
                id: new CardId('inv4'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in hervorragender Lage steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100000),
                ),
                annualRent: new MoneyAmount(5500),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv5" => new InvestitionenCardDefinition(
                id: new CardId('inv5'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einer Seniorinnenanlage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150000),
                ),
                annualRent: new MoneyAmount(8250),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv6" => new InvestitionenCardDefinition(
                id: new CardId('inv6'),
                title: 'Kauf Wohnung',
                description: 'Eine frisch renovierte Wohnung mit solventen Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100000),
                ),
                annualRent: new MoneyAmount(5500),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv7" => new InvestitionenCardDefinition(
                id: new CardId('inv7'),
                title: 'Kauf Haus',
                description: 'Ein Haus in einem Brennpunktviertel steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-175000),
                ),
                annualRent: new MoneyAmount(9625),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv8" => new InvestitionenCardDefinition(
                id: new CardId('inv8'),
                title: 'Kauf Haus',
                description: 'Ein sanierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(11000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv9" => new InvestitionenCardDefinition(
                id: new CardId('inv9'),
                title: 'Kauf Haus',
                description: 'Ein renovierungsbedürftiges Haus steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(11000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv10" => new InvestitionenCardDefinition(
                id: new CardId('inv10'),
                title: 'Kauf Haus',
                description: 'Ein Haus steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(16500),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv11" => new InvestitionenCardDefinition(
                id: new CardId('inv11'),
                title: 'Kauf Haus',
                description: 'Ein Haus in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-350000),
                ),
                annualRent: new MoneyAmount(19250),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv12" => new InvestitionenCardDefinition(
                id: new CardId('inv12'),
                title: 'Kauf Haus',
                description: 'Ein neu renoviertes Haus steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-400000),
                ),
                annualRent: new MoneyAmount(22000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv13" => new InvestitionenCardDefinition(
                id: new CardId('inv13'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Sudierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150000),
                ),
                annualRent: new MoneyAmount(9000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv14" => new InvestitionenCardDefinition(
                id: new CardId('inv14'),
                title: 'Kauf Wohnung',
                description: 'Eine frisch renovierte Wohnung mit solventen Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-270000),
                ),
                annualRent: new MoneyAmount(16200),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv15" => new InvestitionenCardDefinition(
                id: new CardId('inv15'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einer SeniorInnenanlage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-280000),
                ),
                annualRent: new MoneyAmount(16800),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv16" => new InvestitionenCardDefinition(
                id: new CardId('inv16'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in hervorragender Lage steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-290000),
                ),
                annualRent: new MoneyAmount(17400),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv17" => new InvestitionenCardDefinition(
                id: new CardId('inv17'),
                title: 'Kauf Wohnung',
                description: 'Ein renoviertes Loft  mit DINKs (Double Income, No Kids) –Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(18000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv18" => new InvestitionenCardDefinition(
                id: new CardId('inv18'),
                title: 'Kauf Haus',
                description: 'Ein sanierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-120000),
                ),
                annualRent: new MoneyAmount(7200),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv19" => new InvestitionenCardDefinition(
                id: new CardId('inv19'),
                title: 'Kauf Haus',
                description: 'Ein renovierungsbedürftiges Haus steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(12000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv20" => new InvestitionenCardDefinition(
                id: new CardId('inv20'),
                title: 'Kauf Haus',
                description: 'Ein Haus in einem Brennpunktviertel steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(12000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv21" => new InvestitionenCardDefinition(
                id: new CardId('inv21'),
                title: 'Kauf Haus',
                description: 'Ein neu renoviertes Haus steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-250000),
                ),
                annualRent: new MoneyAmount(15000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv22" => new InvestitionenCardDefinition(
                id: new CardId('inv22'),
                title: 'Kauf Haus',
                description: 'Ein Haus in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-280000),
                ),
                annualRent: new MoneyAmount(16800),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv23" => new InvestitionenCardDefinition(
                id: new CardId('inv23'),
                title: 'Kauf Haus',
                description: 'Ein Haus im Grünen soll verkauft werden. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(18000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv24" => new InvestitionenCardDefinition(
                id: new CardId('inv24'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Studierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-155000),
                ),
                annualRent: new MoneyAmount(10000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv25" => new InvestitionenCardDefinition(
                id: new CardId('inv25'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einer Senior:innenanlage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-290000),
                ),
                annualRent: new MoneyAmount(18850),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv26" => new InvestitionenCardDefinition(
                id: new CardId('inv26'),
                title: 'Kauf Wohnung',
                description: 'Eine frisch renovierte Wohnung mit solventen Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-290000),
                ),
                annualRent: new MoneyAmount(18850),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv27" => new InvestitionenCardDefinition(
                id: new CardId('inv27'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in hervorragender Lage steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-310000),
                ),
                annualRent: new MoneyAmount(20150),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv28" => new InvestitionenCardDefinition(
                id: new CardId('inv28'),
                title: 'Kauf Wohnung',
                description: 'Ein renoviertes Loft  mit DINKs (Double Income, No Kids) –Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-350000),
                ),
                annualRent: new MoneyAmount(22750),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv29" => new InvestitionenCardDefinition(
                id: new CardId('inv29'),
                title: 'Kauf Haus',
                description: 'Ein sanierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-160000),
                ),
                annualRent: new MoneyAmount(10400),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv30" => new InvestitionenCardDefinition(
                id: new CardId('inv30'),
                title: 'Kauf Haus',
                description: 'Ein neu renoviertesHaus steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-255000),
                ),
                annualRent: new MoneyAmount(16575),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv31" => new InvestitionenCardDefinition(
                id: new CardId('inv31'),
                title: 'Kauf Haus',
                description: 'Eine Haus in renovierungsbedürftigem Zustand steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-280000),
                ),
                annualRent: new MoneyAmount(18200),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv32" => new InvestitionenCardDefinition(
                id: new CardId('inv32'),
                title: 'Kauf Haus',
                description: 'Ein Haus in einem Brennpunktviertel steht zum Verkauf. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(19500),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv33" => new InvestitionenCardDefinition(
                id: new CardId('inv33'),
                title: 'Kauf Haus',
                description: 'Ein Haus wird zum Verkauf angeboten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-330000),
                ),
                annualRent: new MoneyAmount(21450),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv34" => new InvestitionenCardDefinition(
                id: new CardId('inv34'),
                title: 'Kauf Haus',
                description: 'Ein Haus in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-380000),
                ),
                annualRent: new MoneyAmount(24700),
                immobilienTyp: ImmobilienType::HAUS,
            ),
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
                title: 'Kulturabo',
                description: 'Schließe ein Kulturabo deiner Stadt ab und besuche regelmäßig interessante Premieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-750),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk2" => new KategorieCardDefinition(
                id: new CardId('buk2'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Nachhilfe',
                description: 'Du willst deine Noten verbessern und gehst deshalb zur Nachhilfe.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk3" => new KategorieCardDefinition(
                id: new CardId('buk3'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Bücher lesen',
                description: 'Mach es wie ein erfolgreicher CEO und lies dieses Jahr jede Woche ein neues Buch.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk4" => new KategorieCardDefinition(
                id: new CardId('buk4'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Erste-Hilfe-Kurs',
                description: 'Du absolvierst einen Erste-Hilfe-Kurs, um im Notfall richtig handeln zu können.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk5" => new KategorieCardDefinition(
                id: new CardId('buk5'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Online-Seminar',
                description: 'Um deine Computerkenntnisse zu verbessern, besuchst du ein Online-Seminar.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk6" => new KategorieCardDefinition(
                id: new CardId('buk6'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Bibliotheksausweis',
                description: 'Du meldest dich in der Bibliothek an und nutzt die Möglichkeit, regelmäßig Bücher auszuleihen, um dich weiterzubilden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk7" => new KategorieCardDefinition(
                id: new CardId('buk7'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'VHS-Kurs',
                description: 'Du besuchst einen Kurs an der Volkshochschule.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-110),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk8" => new KategorieCardDefinition(
                id: new CardId('buk8'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Gedächtnistraining',
                description: 'Trainiere dein Gedächtnis täglich 20 Minuten, um deine geistige Fitness zu erhalten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk9" => new KategorieCardDefinition(
                id: new CardId('buk9'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Podcast',
                description: 'Um dich im Bereich Finanzen fortzubilden, hörst du regelmäßig einen Podcast. Das nimmt viel Zeit in Anspruch.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk10" => new KategorieCardDefinition(
                id: new CardId('buk10'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprach-Lern-App',
                description: 'Mithilfe einer Sprachlern-App versuchst du, dir erste Grundkenntnisse in einer dir unbekannten Sprache anzueignen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-250),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk11" => new KategorieCardDefinition(
                id: new CardId('buk11'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Startup-Wettbewerb',
                description: 'Du machst bei einem Startup-Wettbewerb mit und steckst eigenes Kapital in die Entwicklung eines Prototyps.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk12" => new KategorieCardDefinition(
                id: new CardId('buk12'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachreise nach England',
                description: 'Reise nach England, um deine Sprachkenntnisse zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk13" => new KategorieCardDefinition(
                id: new CardId('buk13'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Qualifizierungsmaßnahme',
                description: 'Du absolvierst eine Qualifizierungsmaßnahme an einer Abendschule.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk14" => new KategorieCardDefinition(
                id: new CardId('buk14'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Persöhnlichkeitscoaching',
                description: 'Um dein Auftreten zu stärken, entscheidest du dich für ein Persönlichkeitscoaching. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk15" => new KategorieCardDefinition(
                id: new CardId('buk15'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Nachrichten lesen',
                description: 'Informiere dich jeden Morgen ausführlich über aktuelle Ereignisse und Nachrichten in der Welt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk16" => new KategorieCardDefinition(
                id: new CardId('buk16'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mehr Erfolg durch Fitness',
                description: 'Wer regelmäßig Sport treibt, ist im Beruf erfolgreicher und hat größere Chancen aufzusteigen!',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk17" => new KategorieCardDefinition(
                id: new CardId('buk17'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Blogbeitrag',
                description: 'Verbessere deine Außendarstellung, indem du über deine beruflichen Erfahrungen bloggst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk18" => new KategorieCardDefinition(
                id: new CardId('buk18'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachkurs',
                description: 'Belege einen dreimonatigen Sprachkurs in England.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk19" => new KategorieCardDefinition(
                id: new CardId('buk19'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ausbildung Segellehkraft',
                description: 'Verwirkliche deinen Traum und absolviere eine Ausbildung zur Segellehrkraft. Dabei lernst du nicht nur technisches Wissen, sondern auch viel über gruppendynamische Prozesse.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk20" => new KategorieCardDefinition(
                id: new CardId('buk20'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Networking',
                description: 'Knüpfe wertvolle berufliche Kontakte und erweitere deinen Horizont durch die Teilnahme an internationalen Konferenzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8500),
                    bildungKompetenzsteinChange: +2,
                ),
            ),
            "buk21" => new KategorieCardDefinition(
                id: new CardId('buk21'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ausbildung zur Skilehrkraft',
                description: 'Erfülle dir deinen Traum und absolviere eine Ausbildung zur Skilehrkraft. Neben technischem Wissen eignest du dir dabei auch geografische und pädagogische Kenntnisse an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk22" => new KategorieCardDefinition(
                id: new CardId('buk22'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Bergtourleitung',
                description: 'Verwirkliche deinen Traum und absolviere die Ausbildung zur Bergtourleitung. Dabei erwirbst du nicht nur technisches, sondern auch geografisches und pädagogisches Wissen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk23" => new KategorieCardDefinition(
                id: new CardId('buk23'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Jagdverein',
                description: 'Werde Mitglied im Jagdverein und triff dich mit anderen engagierten Nachwuchstalenten in der freien Natur.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5700),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk24" => new KategorieCardDefinition(
                id: new CardId('buk24'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Karriere Booster: Chinesisch',
                description: 'Stell dich der Herausforderung der anspruchsvollsten Sprache der Welt: Bei deinem Aufenthalt in China kannst du dein Chinesisch deutlich verbessern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk25" => new KategorieCardDefinition(
                id: new CardId('buk25'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Übungsleiterschein im Kinderturnen',
                description: 'Erfülle dir deinen Traum und mache deinen Übungsleiterschein im Kinderturnen. Neben technischem und pädagogischem Wissen eignest du dir dabei auch Kenntnisse über gruppendynamische Prozesse an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk26" => new KategorieCardDefinition(
                id: new CardId('buk26'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Reise',
                description: 'Reise in ein fremdes Land, um deinen kulturellen Horizont zu erweitern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk27" => new KategorieCardDefinition(
                id: new CardId('buk27'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ortswechsel',
                description: 'Zieh für einen Neustart in eine neue Stadt – auch wenn der Umzug Geld kostet.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk28" => new KategorieCardDefinition(
                id: new CardId('buk28'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Auslandsaufenthalt',
                description: 'Für deine Karriere ziehst du für sechs Monate nach Singapur.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1800),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk29" => new KategorieCardDefinition(
                id: new CardId('buk29'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Feierabendgetränk',
                description: 'Mit deiner Kollegschaft ziehst du regelmäßig nach dem Feierabend um die Häuser.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk30" => new KategorieCardDefinition(
                id: new CardId('buk30'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder abgeschlossenen Weiterbildung wächst nicht nur dein Wissen, sondern auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk31" => new KategorieCardDefinition(
                id: new CardId('buk31'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Seminarwochenende',
                description: 'Du nimmst an einem Seminarwochenende der Börse Frankfurt teil, um verschiedene Investmentstrategien kennenzulernen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1100),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk33" => new KategorieCardDefinition(
                id: new CardId('buk33'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ortswechsel',
                description: 'Du ziehst in ein anderes Bundesland und hast so die Chance, dein Privatleben neu zu gestalten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk34" => new KategorieCardDefinition(
                id: new CardId('buk34'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kongresseinladung',
                description: 'Du wirst eingeladen, auf einem wichtigen Kongress über deine beruflichen Erfahrungen zu sprechen. Die Vorbereitung der Rede nimmt viel Zeit in Anspruch, macht dich aber sehr bekannt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk35" => new KategorieCardDefinition(
                id: new CardId('buk35'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Networking',
                description: 'Baue deine Geschäftsbeziehungen sorgfältig aus, um deine berufliche Laufbahn zu fördern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk36" => new KategorieCardDefinition(
                id: new CardId('buk36'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Trainerschein als Fluglehrkraft',
                description: 'Verwirkliche deinen Traum und werde Fluglehrkraft. Neben technischem Wissen baust du dir fundierte Kenntnisse über aerodynamische Prozesse auf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-35000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk38" => new KategorieCardDefinition(
                id: new CardId('buk38'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachreise',
                description: '"Absolviere eine Sprachreise, um deine Kommunikationsfähigkeiten zu verbessern. Du wirst dabei von einer kompetenten Sprachlehrkraft optimal und individuell betreut. "',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-22000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk39" => new KategorieCardDefinition(
                id: new CardId('buk39'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachkurs',
                description: 'Absolviere einen drei Monate langen Sprachkurs im Ausland.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-18000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk40" => new KategorieCardDefinition(
                id: new CardId('buk40'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Jagdschein',
                description: 'Verwirkliche deinen Traum und erwirb deinen Jagdschein. Dabei eignest du dir nicht nur umfassendes Wissen über Flora und Fauna an, sondern auch wertvolle geografische Kenntnisse.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-17000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk41" => new KategorieCardDefinition(
                id: new CardId('buk41'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'längere Reise',
                description: '"Nutze die Gelegenheit, eine ausgedehnte Reise in ein fremdes Land zu unternehmen, um deinen kulturellen Horizont nachhaltig zu erweitern."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-16000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk42" => new KategorieCardDefinition(
                id: new CardId('buk42'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mitgliedschaft Kulturverein',
                description: 'Werde Mitglied im Kulturverein und genieße Treffen mit Gleichgesinnten in Opernhäusern rund um die Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-13500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk43" => new KategorieCardDefinition(
                id: new CardId('buk43'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kulturmäzen',
                description: 'Engagiere dich als Kulturmäzen deiner Stadt und besuche regelmäßig interessante Premieren, die durch dein Sponsoring ermöglicht werden.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk44" => new KategorieCardDefinition(
                id: new CardId('buk44'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du wolltest schon immer dein kreatives Talent fördern und belegst deshalb eine Weiterbildung in Kunstgeschichte.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk45" => new KategorieCardDefinition(
                id: new CardId('buk45'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Organisation Technik-Camps',
                description: 'Unterstütze eine Schule, indem du ein Technik-Camp für Jugendliche organisierst. Dieses Engagement kannst du dir im Lebenslauf anrechnen lassen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk46" => new KategorieCardDefinition(
                id: new CardId('buk46'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Verkaufscoaching',
                description: 'Verkaufen ist eine Schlüsselkompetenz – wer sich und seine Ideen überzeugend präsentiert, kommt schneller ans Ziel. Du entscheidest dich deshalb für ein professionelles Verkaufscoaching.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(9500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk47" => new KategorieCardDefinition(
                id: new CardId('buk47'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Auslandsaufenthalt',
                description: 'Für deine Karriere ziehst du für sechs Monate nach New York.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12400),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk48" => new KategorieCardDefinition(
                id: new CardId('buk48'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Karriere Booster: Französisch',
                description: 'Nutze die schönste Sprache der Welt: Ein längerer Aufenthalt in Paris gibt dir die Chance, dein Schulfranzösisch aufzufrischen und zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk49" => new KategorieCardDefinition(
                id: new CardId('buk49'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sommerfest',
                description: 'Du lädst deine Kollegschaft zu einem Sommerfest in dein Ferienhaus am See ein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5600),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk50" => new KategorieCardDefinition(
                id: new CardId('buk50'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Onlinekurs Universität',
                description: 'Immer mehr Universitäten stellen ihre Vorlesungen online zur Verfügung. Profitiere davon und besuche einen Kurs an einer amerikanischen Eliteuniversität.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk51" => new KategorieCardDefinition(
                id: new CardId('buk51'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Außendarstellung',
                description: 'Optimiere deinen Auftritt: Lass deine Bewerbungsunterlagen und deine Online-Präsenz professionell überarbeiten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk52" => new KategorieCardDefinition(
                id: new CardId('buk52'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Netzwerk',
                description: 'Vernetze dich beruflich und entwickle dich auf internationalen Konferenzen fachlich sowie persönlich weiter.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk53" => new KategorieCardDefinition(
                id: new CardId('buk53'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Wertekongress',
                description: 'Du nimmst an einem Wertekongress für Führungskräfte teil und investierst dafür Zeit und finanzielle  Ressourcen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2700),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk54" => new KategorieCardDefinition(
                id: new CardId('buk54'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder abgeschlossenen Weiterbildung wächst nicht nur dein Wissen, sondern auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk55" => new KategorieCardDefinition(
                id: new CardId('buk55'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Zeitungsabonnement',
                description: 'Informiere dich jeden Morgen über aktuelle Ereignisse in der Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk56" => new KategorieCardDefinition(
                id: new CardId('buk56'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Programmierkurs',
                description: 'Mache dich mit einem Programmierkurs an der Abendschule fit für die Herausforderungen der digitalen Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1300),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk57" => new KategorieCardDefinition(
                id: new CardId('buk57'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Politik-News',
                description: 'Um dich politisch und wirtschaftlich auf dem Laufenden zu halten, abonnierst du eine individuell zusammengestellte Auswahl wichtiger Berichte und Reportagen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1250),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk58" => new KategorieCardDefinition(
                id: new CardId('buk58'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Networking ',
                description: 'Baue deine Geschäftsbeziehungen sorgfältig aus, um deine berufliche Laufbahn zu fördern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-700),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk59" => new KategorieCardDefinition(
                id: new CardId('buk59'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'wöchentlicher Podcast',
                description: 'Mach es wie erfolgreiche CEOs: Höre wöchentlich einen neuen Podcast und halte die zentralen Erkenntnisse fest – so entwickelst du dein Wissen stetig weiter.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk61" => new KategorieCardDefinition(
                id: new CardId('buk61'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kongresseinladung',
                description: 'Du wirst eingeladen, auf einem wichtigen Kongress über deine beruflichen Erfahrungen zu sprechen. Die Vorbereitung der Rede nimmt viel Zeit in Anspruch, macht dich aber sehr bekannt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk62" => new KategorieCardDefinition(
                id: new CardId('buk62'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Austauschprogramm',
                description: 'Du nimmst an einem Austauschprogramm teil und arbeitest zwei Monate in einem fremden Unternehmen. Die Vorbereitung für den Auslandsaufenthalt kostet dich Zeit. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk63" => new KategorieCardDefinition(
                id: new CardId('buk63'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mehr Erfolg durch Fitness ',
                description: 'Wer regelmäßig Sport treibt, ist im Beruf erfolgreicher und hat größere Chancen aufzusteigen!',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk64" => new KategorieCardDefinition(
                id: new CardId('buk64'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Zeitmanagement',
                description: 'Die neue Lebensphase stellt dich vor Herausforderungen und erfordert besseres Zeitmanagement – sonst bleiben deine Weiterbildungsziele unerreicht.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk65" => new KategorieCardDefinition(
                id: new CardId('buk65'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Gedächtnistraining',
                description: 'Nimm dir jeden Tag 10 Minuten für Gedächtnistraining, um deine geistige Leistungsfähigkeit zu bewahren.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk68" => new KategorieCardDefinition(
                id: new CardId('buk68'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mentorenprogramm',
                description: 'Bewirb dich für ein Mentorenprogramm und triff deinen Mentor, einen renommierten CEO, wöchentlich zum Mittagessen. Die Kosten übernimmst du als Zeichen deiner Dankbarkeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-47000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk69" => new KategorieCardDefinition(
                id: new CardId('buk69'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kulturmäzen',
                description: 'Engagiere dich als Kulturmäzen deiner Stadt und besuche regelmäßig interessante Premieren, die durch dein Sponsoring ermöglicht werden.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk70" => new KategorieCardDefinition(
                id: new CardId('buk70'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'TED-Talks',
                description: 'Reise zu den großen TED-Talks, um stets über die neuesten Innovationen informiert zu sein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-22000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk71" => new KategorieCardDefinition(
                id: new CardId('buk71'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'bekannte Persönlichkeiten',
                description: 'Lade regelmäßig inspirierende Persönlichkeiten ein und lerne direkt von deren Erfahrungen und Werdegang.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-17000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk72" => new KategorieCardDefinition(
                id: new CardId('buk72'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Coach Strategieentwicklung',
                description: 'Engagiere einen Coach, der dich in deiner Strategieentwicklung unterstützt. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-16800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk73" => new KategorieCardDefinition(
                id: new CardId('buk73'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Organisation KI-Camp',
                description: 'Unterstütze eine Schule, indem du ein KI-Camp für Jugendliche organisierst. Dieses Engagement kannst du dir im Lebenslauf anrechnen lassen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk74" => new KategorieCardDefinition(
                id: new CardId('buk74'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Teambuildingworkshop',
                description: 'Du lädst dein Team zu einem Teambuilding-Workshop in ein modernes Strandhaus ein und übernimmst sämtliche Kosten. Das Ergebnis: ein gestärktes Wir-Gefühl und spürbar steigende Umsätze.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk75" => new KategorieCardDefinition(
                id: new CardId('buk75'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'archäologische Expedition',
                description: 'Erfülle deine Abenteuerlust mit einer archäologischen Expedition – betreut von einem erfahrenen Guide, der individuell auf dich eingeht.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk76" => new KategorieCardDefinition(
                id: new CardId('buk76'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachbgeleitung',
                description: 'Profitiere von professioneller Sprachbegleitung für verhandlungssichere Kommunikation im internationalen Kontext.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-13200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk77" => new KategorieCardDefinition(
                id: new CardId('buk77'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder abgeschlossenen Weiterbildung wächst nicht nur dein Wissen, sondern auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11100),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk78" => new KategorieCardDefinition(
                id: new CardId('buk78'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Auslandsaufenthalt',
                description: '"Für deinen nächsten Karriereschritt ziehst du für zwei Jahre nach Hongkong und tauchst dort in neue berufliche Herausforderungen ein."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10400),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk79" => new KategorieCardDefinition(
                id: new CardId('buk79'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kurs Töpferei',
                description: 'Du wolltest dein kreatives Talent schon immer fördern und belegst deshalb einen Anfänger- sowie einen Aufbaukurs in Töpferei.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8200),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk80" => new KategorieCardDefinition(
                id: new CardId('buk80'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Briefing',
                description: 'Lass dir individuelle Briefings zum aktuellen Weltgeschehen zuschicken – exakt auf deine Bedürfnisse zugeschnitten und mit allen für dich relevanten Informationen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk81" => new KategorieCardDefinition(
                id: new CardId('buk81'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'internationaler Kongress',
                description: 'Du wirst eingeladen, auf einem internationalen Kongress zu sprechen, musst jedoch die Reisekosten selbst tragen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk82" => new KategorieCardDefinition(
                id: new CardId('buk82'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ernährungsumstellung',
                description: 'Mit der Unterstützung eines Ernährungs- und Fitnesscoachs bringst du dich in Topform, um mit neuer Power und Energie den nächsten Karriereschritt zu meistern.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk83" => new KategorieCardDefinition(
                id: new CardId('buk83'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Wertekongress',
                description: 'Du nimmst an einem Wertekongress für Führungskräfte teil und investierst dafür Zeit und finanzielle  Ressourcen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6500),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk84" => new KategorieCardDefinition(
                id: new CardId('buk84'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder abgeschlossenen Weiterbildung wächst nicht nur dein Wissen, sondern auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk85" => new KategorieCardDefinition(
                id: new CardId('buk85'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Onlinekurs Universität',
                description: 'Immer mehr Universitäten stellen ihre Vorlesungen online zur Verfügung. Profitiere davon und besuche einen Kurs an einer amerikanischen Eliteuniversität.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7500),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk86" => new KategorieCardDefinition(
                id: new CardId('buk86'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Tagesseminar',
                description: 'Positionierung ist das A und O. Deshalb besuchst du ein Tagesseminar, in dem du lernst, dich gezielt in deiner Branche zu positionieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk88" => new KategorieCardDefinition(
                id: new CardId('buk88'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Austauschprogramm',
                description: 'Du nimmst an einem Austauschprogramm teil und arbeitest zwei Monate in einem fremden Unternehmen. Die Vorbereitung für den Auslandsaufenthalt kostet dich Zeit. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk89" => new KategorieCardDefinition(
                id: new CardId('buk89'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kongresseinladung',
                description: 'Du wirst eingeladen, auf einem wichtigen Kongress über deine beruflichen Erfahrungen zu sprechen. Die Vorbereitung der Rede nimmt viel Zeit in Anspruch, macht dich aber sehr bekannt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk90" => new KategorieCardDefinition(
                id: new CardId('buk90'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Gedächtnistraining',
                description: 'Trainiere dein Gedächtnis täglich 10 Minuten, um geistig fit zu bleiben.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk92" => new KategorieCardDefinition(
                id: new CardId('buk92'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mentoringnetzwerk',
                description: 'Gründe ein Mentoringnetzwerk für die jüngere Generation deiner Branche und sammle dabei wertvolles Wissen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk93" => new KategorieCardDefinition(
                id: new CardId('buk93'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Bergtourleitung',
                description: 'Erfülle dir deinen Traum und bilde dich zur Bergtourleitung aus. Dabei erwirbst du nicht nur technisches Wissen, sondern auch geografische und pädagogische Kompetenzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-13200),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "suf1" => new KategorieCardDefinition(
                id: new CardId('suf1'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die Menschen mit Beeinträchtigung einen unvergesslichen Urlaub ermöglicht. Die anfallenden Kosten für dich trägst du dabei selbst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf2" => new KategorieCardDefinition(
                id: new CardId('suf2'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Musikverein',
                description: 'Du meldest dich im Musikverein an. Dafür musst du ein hochwertiges Instrument kaufen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf3" => new KategorieCardDefinition(
                id: new CardId('suf3'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Volleyballverein',
                description: 'Du meldest dich im Volleyballverein an und bist fortan Teil einer engagierten Mannschaft.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf4" => new KategorieCardDefinition(
                id: new CardId('suf4'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Kantine',
                description: 'Du hast aufgehört zu kochen und nutzt stattdessen nur noch die Angebote der Cafeteria. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf5" => new KategorieCardDefinition(
                id: new CardId('suf5'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende Tierheim',
                description: 'Regelmäßig spendest du bei deinen Einkäufen Tiernahrung an die umliegenden Tierheime.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf6" => new KategorieCardDefinition(
                id: new CardId('suf6'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Nachbarschaftsgarten',
                description: 'Du engagierst dich für einen gemeinschaftlich betriebenen Nachbarschaftsgarten, in dem Ernte, Kosten und Risiken gemeinsam getragen werden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf7" => new KategorieCardDefinition(
                id: new CardId('suf7'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Valentinstag',
                description: 'Obwohl du am Valentinstag Single bist, beschließt du, jedem glücklichen Paar, das dir heute begegnet, eine Rose zu schenken.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-80),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf8" => new KategorieCardDefinition(
                id: new CardId('suf8'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Besuch Pflegeheim',
                description: 'Du besuchst regelmäßig eine Pflegeeinrichtung und veranstaltest dort einen geselligen Brettspielabend für die Bewohnenden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf9" => new KategorieCardDefinition(
                id: new CardId('suf9'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem',
                description: 'Du triffst dich regelmäßig mit einem Erasmus-Studierenden zum Sprachtandem und lernst so viel über andere Sprachen und Kulturen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf10" => new KategorieCardDefinition(
                id: new CardId('suf10'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'kostenlose Nachhilfe',
                description: 'Du gibst sozial benachteiligten Kindern Nachhilfe, um ihre schulischen Chancen zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf11" => new KategorieCardDefinition(
                id: new CardId('suf11'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spazieren mit Hunden',
                description: 'Du hilfst dem örtlichen Tierheim ehrenamtlich und übernimmst dreimal pro Woche die Gassirunden mit den Hunden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf12" => new KategorieCardDefinition(
                id: new CardId('suf12'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Kleidertauschparty',
                description: 'Du bringst Ordnung in deinen Kleiderschrank und nimmst an einer Kleidertauschparty teil.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf13" => new KategorieCardDefinition(
                id: new CardId('suf13'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Einkaufsgemeinschaft',
                description: '"Du organisierst eine Einkaufsgemeinschaft in deiner Nachbarschaft – ihr bestellt gesammelt und lasst umweltschonend liefern."',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf14" => new KategorieCardDefinition(
                id: new CardId('suf14'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Plattform Mitfahrgelegenheit',
                description: 'Du startest eine Mitfahrplattform für den ländlichen Raum – für weniger Emissionen und mehr Miteinander.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf15" => new KategorieCardDefinition(
                id: new CardId('suf15'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Staubsauger-Roboter',
                description: 'Du legst dir einen Saugroboter zu und genießt ein staubfreies Zuhause ganz ohne Aufwand.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf16" => new KategorieCardDefinition(
                id: new CardId('suf16'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fußballverein',
                description: 'Um fit zu bleiben, trittst du einem Fußballverein bei und stattest dich mit hochwertiger Sportkleidung aus.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf17" => new KategorieCardDefinition(
                id: new CardId('suf17'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Flüchtlingspatenschaft',
                description: 'Du übernimmst eine Patenschaft für eine geflüchtete Person.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf18" => new KategorieCardDefinition(
                id: new CardId('suf18'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Du setzt einen Flyer auf, der über demokratische Werte informiert, und investierst eigenes Geld in den Druck.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf19" => new KategorieCardDefinition(
                id: new CardId('suf19'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Brief an Regierung',
                description: '"Regieren ist kein einfacher Job. Die Presse schießt zunehmend gegen das Kanzleramt. Du nimmst dir Zeit, einen ermutigenden Brief an unser Regierungsoberhaupt  zu schreiben und schickst ihn mit einigen Köstlichkeiten zur Stärkung nach Berlin. "',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf20" => new KategorieCardDefinition(
                id: new CardId('suf20'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'SteuerberaterIn',
                description: 'Du verlierst langsam den Überblick über deine Unterlagen der letzten Jahre – Zeit, eine Steuerberaterin zu engagieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf21" => new KategorieCardDefinition(
                id: new CardId('suf21'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Food-Sharing',
                description: 'Durch deine Food-Sharing-Aktivitäten lernst du viele neue Leute kennen und gibst weniger Geld für Essen aus. Der Zeitaufwand ist allerdings nicht ohne.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf22" => new KategorieCardDefinition(
                id: new CardId('suf22'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendenmarathon',
                description: 'Du engagierst dich bei einem Spendenmarathon für krebskranke Kinder und musst viel Zeit in die Suche nach Sponsoren investieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf23" => new KategorieCardDefinition(
                id: new CardId('suf23'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Der örtliche Turnverein sucht händeringend nach Unterstützung. Du erklärst dich bereit, ehrenamtlich als Betreuungsperson beim Mutter-Kind-Turnen auszuhelfen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf24" => new KategorieCardDefinition(
                id: new CardId('suf24'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'CleanUp Aktion',
                description: 'Du setzt dich ehrenamtlich für eine saubere Umwelt ein und hilfst bei einer Clean-up-Aktion in deiner Stadt mit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf25" => new KategorieCardDefinition(
                id: new CardId('suf25'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sozialhilfe',
                description: 'Du stellst eine Pflegeperson ein, die sich um deine Großeltern kümmert, damit du mehr Freiraum für dich hast.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf26" => new KategorieCardDefinition(
                id: new CardId('suf26'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Haushaltshilfe',
                description: 'Du stellst eine Haushaltshilfe ein, damit du mehr Zeit für dich selbst hast.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf27" => new KategorieCardDefinition(
                id: new CardId('suf27'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schlaftracking',
                description: 'Du richtest dein Schlafzimmer nach den neuesten Erkenntnissen der Schlafforschung ein. Dein Schlaf wird dadurch deutlich tiefer, und tagsüber fühlst du dich viel erholter.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf28" => new KategorieCardDefinition(
                id: new CardId('suf28'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft',
                description: 'Du übernimmst die Patenschaft für drei Kinder in Moldawien und bleibst durch regelmäßige Nachrichten mit ihnen verbunden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf29" => new KategorieCardDefinition(
                id: new CardId('suf29'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Reinigungskraft',
                description: 'Du möchtest mehr Zeit mit deinen Liebsten verbringen und entscheidest dich daher, eine Reinigungskraft zu engagieren, die deine Wohnung künftig sauber hält.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf30" => new KategorieCardDefinition(
                id: new CardId('suf30'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: '"Stipendium in Indonesien"',
                description: 'Du übernimmst die Stipendiumskosten für zwei Geschwister in Indonesien.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf31" => new KategorieCardDefinition(
                id: new CardId('suf31'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- & SteuerberaterIn',
                description: 'Auf Empfehlung engagierst du eine kompetente Finanz- und Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf32" => new KategorieCardDefinition(
                id: new CardId('suf32'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Interventionsprogramm ',
                description: 'Du planst ein Interventionsprogramm gegen häusliche Gewalt und trägst dabei die Kosten für den Veranstaltungsraum und den Moderationskoffer selbst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf33" => new KategorieCardDefinition(
                id: new CardId('suf33'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Bio-Essen',
                description: 'Da du keine Lust mehr aufs Kochen hast, bestellst du dir lieber hochwertiges Bio-Essen nach Hause.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf34" => new KategorieCardDefinition(
                id: new CardId('suf34'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fitness',
                description: 'Du meldest dich im Fitnessstudio an und nimmst dir vor, dreimal pro Woche mit einem Personaltrainer zu trainieren. Im kommenden Jahr wirst du deutlich seltener krank sein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf35" => new KategorieCardDefinition(
                id: new CardId('suf35'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Hausverwaltung',
                description: 'Um mehr Freiraum zu haben, beauftragst du eine Hausverwaltung mit der Betreuung deiner Mietwohnung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf36" => new KategorieCardDefinition(
                id: new CardId('suf36'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Aufklärung Menschenhandel',
                description: 'Du gehst auf die Straße und klärst Menschen über moderne Sklaverei auf. Menschenhandel ist leider nicht Vergangenheit, sondern traurige Gegenwart. Dafür kaufst du Plakate und Flyer.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf37" => new KategorieCardDefinition(
                id: new CardId('suf37'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Webseite zur Nachbarschaftshilfe',
                description: 'Du entwickelst eine Webseite, die Menschen aus deiner Nachbarschaft unkompliziert miteinander vernetzt. Die regelmäßigen Kosten für das Hosting trägst du selbst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-400),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf38" => new KategorieCardDefinition(
                id: new CardId('suf38'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Geschenke Obdachlose',
                description: 'Du verteilst Nikolausgeschenke an Obdachlose in deiner Stadt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf39" => new KategorieCardDefinition(
                id: new CardId('suf39'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf40" => new KategorieCardDefinition(
                id: new CardId('suf40'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf41" => new KategorieCardDefinition(
                id: new CardId('suf41'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Up-Cycling',
                description: 'Indem du alte Geräte im Repair-Café upcyclest, sparst du dir die Kosten für neue Anschaffungen und lernst gleichzeitig neue Leute kennen. Allerdings ist das Reparieren zeitaufwendig.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf42" => new KategorieCardDefinition(
                id: new CardId('suf42'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf43" => new KategorieCardDefinition(
                id: new CardId('suf43'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Projektteilnahme',
                description: 'Du machst bei einem 500-Euro-Projekt mit. Ziel ist es, 500 € kreativ zu erwirtschaften und an ein Projekt deiner Wahl zu spenden. #füreinebessereWelt',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf44" => new KategorieCardDefinition(
                id: new CardId('suf44'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pause Investititonen',
                description: 'Du möchtest dich nicht länger vom Auf und Ab der Finanzmärkte stressen lassen und legst deshalb eine Investitionspause ein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf45" => new KategorieCardDefinition(
                id: new CardId('suf45'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fundraisingaktion',
                description: 'Du organisierst eine Fundraisingaktion für die Nothilfe nach Naturkatastrophen, was viel Zeit in Anspruch nimmt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf46" => new KategorieCardDefinition(
                id: new CardId('suf46'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Petition aufsetzen',
                description: 'Du setzt eine Petition für das Bleiberecht von Geflüchteten in Deutschland auf, was viel Zeit erfordert.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf47" => new KategorieCardDefinition(
                id: new CardId('suf47'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Du übernimmst einen Vorstandsposten im Tennisverein, was viel Zeit in Anspruch nimmt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf48" => new KategorieCardDefinition(
                id: new CardId('suf48'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sterbebegleitung',
                description: 'Du beschließt, einen ehrenamtlichen Kurs zur Sterbebegleitung zu machen, um dich in diesem Bereich zu engagieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf49" => new KategorieCardDefinition(
                id: new CardId('suf49'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Arbeit EineWeltLaden',
                description: 'Du beschließt, dich wöchentlich im Eine-Welt-Laden zu engagieren, der fair gehandelte Produkte anbietet.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf50" => new KategorieCardDefinition(
                id: new CardId('suf50'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Baumpflanzaktion',
                description: 'Du machst bei einer ehrenamtlichen Baumpflanzaktion in deiner Stadt mit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf51" => new KategorieCardDefinition(
                id: new CardId('suf51'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Insektenhotel',
                description: 'Du engagierst dich ehrenamtlich beim Bau von Insektenhotels, um Lebensraum für Insekten zu schaffen und die Artenvielfalt zu fördern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf52" => new KategorieCardDefinition(
                id: new CardId('suf52'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Charity-Event',
                description: 'Du richtest ein Charity-Event aus, um benachteiligte Frauen in einer matriarchalen Gemeinschaft in Niger zu unterstützen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf53" => new KategorieCardDefinition(
                id: new CardId('suf53'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Privatsauna',
                description: 'Um dem Alltagsstress zu entfliehen, lässt du dir eine Privatsauna bauen – gemeinsame Entspannungsmomente mit deinen Freunden werden dir neue Energie schenken.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf54" => new KategorieCardDefinition(
                id: new CardId('suf54'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Privatköchin',
                description: 'Deine Kochkünste lassen zu wünschen übrig und die Küche sieht danach oft wie ein Schlachtfeld aus – deshalb engagierst du eine Privatköchin für dich und deine Liebsten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-43000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf55" => new KategorieCardDefinition(
                id: new CardId('suf55'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Haushaltshilfe',
                description: 'Um mehr Zeit für dich und deine Familie zu haben, engagierst du eine Haushaltshilfe.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf56" => new KategorieCardDefinition(
                id: new CardId('suf56'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Familienreise',
                description: 'Du erfüllst dir und deiner Familie einen Traum und buchst eine Reise nach Irland – dort könnt ihr die Ruhe und Schönheit der Natur in vollen Zügen genießen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-34000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf57" => new KategorieCardDefinition(
                id: new CardId('suf57'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schule Fidji',
                description: '"Statt Strandurlaub entscheidest du dich, deinen Jahresurlaub dafür zu nutzen, auf den Fidschi-Inseln eine Schule aufzubauen."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf58" => new KategorieCardDefinition(
                id: new CardId('suf58'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pflegedienst',
                description: 'Du sorgst mit einem Pflegedienst für die notwendige Betreuung deiner Großeltern und nutzt die gemeinsame Zeit bewusst für Spaziergänge und entspannte Gespräche bei Kaffee und Kuchen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf59" => new KategorieCardDefinition(
                id: new CardId('suf59'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sponsoring',
                description: '"Du übernimmst Verantwortung und wirst Sponsor eines Charity-Events, das krebskranken Kindern Hoffnung und Unterstützung schenkt."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf60" => new KategorieCardDefinition(
                id: new CardId('suf60'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: '"Für sechs Monate reist du nach Bangladesch, um an einer neu eröffneten Schule Englisch zu unterrichten und wertvolle Bildungschancen zu schaffen."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf61" => new KategorieCardDefinition(
                id: new CardId('suf61'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Whirlpool',
                description: 'Du lässt dir einen Whirlpool in den Garten bauen, um nach der Arbeit gemütlich entspannen zu können. Überraschenderweise erhältst du nun deutlich mehr Besuch aus der Nachbarschaft.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf62" => new KategorieCardDefinition(
                id: new CardId('suf62'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'persönliche Stylistin',
                description: '"Mit einer persönlichen Stylistin setzt du auf zeitlosen Stil statt Fast Fashion – und vermeidest unnötige Shoppingtouren."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf63" => new KategorieCardDefinition(
                id: new CardId('suf63'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Jahresabo Bio-Essen',
                description: 'Du setzt auf Genuss ohne Aufwand: Ein Bio-Jahresabo versorgt dich täglich mit frischen, gesunden Mahlzeiten – ins Büro und nach Hause geliefert.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf64" => new KategorieCardDefinition(
                id: new CardId('suf64'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft',
                description: 'Du engagierst dich als Pate für drei Kinder in Moldawien und trägst aktiv zu ihrer Schulbildung und Entwicklung bei.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf65" => new KategorieCardDefinition(
                id: new CardId('suf65'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Putzdienst',
                description: '"Du beauftragst einen Reinigungsdienst für dein Chaos, der professionell reinigt und dabei konsequent auf Nachhaltigkeit und Umweltfreundlichkeit achtet."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf66" => new KategorieCardDefinition(
                id: new CardId('suf66'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Au-Pair',
                description: '"Du engagierst eine Au-pair-Betreuung, die dir nicht nur neue Freizeit schenkt, sondern auch einen wertvollen kulturellen Austausch in dein Zuhause bringt."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf67" => new KategorieCardDefinition(
                id: new CardId('suf67'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'plastikfreier Haushalt',
                description: 'Du stellst deinen Haushalt komplett auf plastikfreie Produkte um. Seit du konsequent auf nachhaltige Alternativen setzt, lebst du bewusster und schläfst mit einem ruhigeren Gewissen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf68" => new KategorieCardDefinition(
                id: new CardId('suf68'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Permakultur',
                description: 'Mit deinem Permakultur-Projekt förderst du nachhaltige Anbaumethoden und regenerierst den Boden. Du öffnest die Türen für alle, die mehr über nachhaltige Landwirtschaft lernen möchten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf69" => new KategorieCardDefinition(
                id: new CardId('suf69'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schlaftracking',
                description: 'Mithilfe neuester Schlafforschung gestaltest du dein Schlafzimmer optimal – dein Schlaf ist tiefer und du startest tagsüber voller Energie.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9800),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf70" => new KategorieCardDefinition(
                id: new CardId('suf70'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fitness',
                description: 'Du meldest dich im Fitnessstudio mit Personaltrainer an und nimmst dir fest vor, dreimal pro Woche zu trainieren. Dadurch bist du im kommenden Jahr seltener krank.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf71" => new KategorieCardDefinition(
                id: new CardId('suf71'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- & SteuerberaterIn',
                description: '"Dank einer Empfehlung findest du eine erfahrene Finanz- und Steuerberaterin, die dich kompetent unterstützt."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8700),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf72" => new KategorieCardDefinition(
                id: new CardId('suf72'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende ',
                description: 'Aus deiner Liebe zu Tieren heraus entscheidest du dich, die lokalen Tierheime zu unterstützen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf73" => new KategorieCardDefinition(
                id: new CardId('suf73'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Markt',
                description: 'Du kaufst ausschließlich auf dem Markt ein, um dich gesünder und nachhaltiger zu ernähren. So unterstützt du lokale Produzenten und schonst die Umwelt – und fühlst dich dabei rundum fit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf74" => new KategorieCardDefinition(
                id: new CardId('suf74'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'digitale Tauschbörse',
                description: 'Mit deiner digitalen Tauschbörse förderst du den gemeinschaftlichen Gebrauch von Gartengeräten und Maschinen – nachhaltig und praktisch.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf75" => new KategorieCardDefinition(
                id: new CardId('suf75'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Musikkurs',
                description: 'Du meldest dich für einen Musikkurs an, um deine Fähigkeiten zu verbessern und neue Kontakte zu knüpfen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7600),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf76" => new KategorieCardDefinition(
                id: new CardId('suf76'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Dauerkarte 1.Liga',
                description: 'Mit einer Dauerkarte für deinen Lieblingsverein verpasst du kein Spiel mehr und erlebst die Atmosphäre live im Stadion.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf77" => new KategorieCardDefinition(
                id: new CardId('suf77'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Interventionsprogramm',
                description: '"Du initiierst ein Interventionsprogramm, das Betroffenen häuslicher Gewalt hilft und Prävention fördert."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf78" => new KategorieCardDefinition(
                id: new CardId('suf78'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'persönliche Stylistin',
                description: '"Mit einer persönlichen Stylistin setzt du auf zeitlosen Stil statt Fast Fashion – und vermeidest unnötige Shoppingtouren."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf79" => new KategorieCardDefinition(
                id: new CardId('suf79'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Gründung Gemeinschaftsgarten',
                description: 'Du gründest einen Gemeinschaftsgarten und pachtest eine Fläche, um gemeinsam Blumen, Gemüse und Kräuter nachhaltig anzubauen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf80" => new KategorieCardDefinition(
                id: new CardId('suf80'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Einkäufe',
                description: 'Statt selbst einkaufen zu gehen, nutzt du eine Drohnenlieferung mit nachhaltigen Verpackungen und kurzen Transportwegen. So schonst du die Umwelt und hast mehr Zeit für dich und deine Freunde.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf81" => new KategorieCardDefinition(
                id: new CardId('suf81'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Up-Cycling',
                description: 'Mit deiner Upcycling-Initiative gestaltest du zusammen mit einer Gruppe engagierter Freiwilliger alte Kleidung zu stylischen, nachhaltigen Outfits um.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf82" => new KategorieCardDefinition(
                id: new CardId('suf82'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Geschenke Obdachlose',
                description: 'Mit Nikolausgeschenken bringst du Freude zu den obdachlosen Menschen in deiner Stadt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf83" => new KategorieCardDefinition(
                id: new CardId('suf83'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf84" => new KategorieCardDefinition(
                id: new CardId('suf84'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'SteuerberaterIn',
                description: 'Weil dir die Unterlagen vom letzten Jahr über den Kopf wachsen, holst du dir Unterstützung durch eine Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf85" => new KategorieCardDefinition(
                id: new CardId('suf85'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Betreuung Buchhaltung',
                description: 'Du beauftragst ein Unternehmen mit der Betreuung deiner Buchhaltung und Terminorganisation – so gewinnst du mehr Zeit für die wirklich wichtigen Dinge.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf86" => new KategorieCardDefinition(
                id: new CardId('suf86'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf87" => new KategorieCardDefinition(
                id: new CardId('suf87'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Besuch Pflegeheim',
                description: 'Als Teil deines sozialen Engagements besuchst du eine Pflegeeinrichtung und gestaltest mit den Bewohnenden einen Brettspielabend.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf88" => new KategorieCardDefinition(
                id: new CardId('suf88'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Mentoring Universität',
                description: 'Du engagierst dich an der Universität, um junge Absolventinnen nachhaltig beim Berufseinstieg zu unterstützen und ihre Chancen auf dem Arbeitsmarkt zu stärken.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf89" => new KategorieCardDefinition(
                id: new CardId('suf89'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Mathenachhilfe',
                description: '"Du gibst kostenlose Mathenachhilfe für Kinder in deinem Viertel – dafür nimmst du dir bewusst Zeit."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf90" => new KategorieCardDefinition(
                id: new CardId('suf90'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Training Jugendmannschaft',
                description: 'Du trainierst jetzt die Jugendmannschaft deines Fußballvereins. Seitdem steckst du deine ganze Energie in den Nachwuchs – und nicht mehr in Investitionen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf91" => new KategorieCardDefinition(
                id: new CardId('suf91'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spazieren mit Hunden',
                description: 'Du hilfst im örtlichen Tierheim mit und gehst regelmäßig dreimal pro Woche mit den Hunden Gassi – ein Gewinn für Mensch und Tier.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf92" => new KategorieCardDefinition(
                id: new CardId('suf92'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich regelmäßig in einem Jugendzentrum in einem sozial herausgeforderten Viertel. Die Arbeit ist erfüllend, aber auch zeitintensiv.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf93" => new KategorieCardDefinition(
                id: new CardId('suf93'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendemarathon',
                description: 'Du nimmst an einem Spendemarathon für krebskranke Kinder teil. Die Suche nach Sponsoren ist zeitaufwendig, aber lohnenswert.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf94" => new KategorieCardDefinition(
                id: new CardId('suf94'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem',
                description: 'Mit deiner neuen Nachbarin aus Spanien triffst du dich regelmäßig zum Sprachtandem und entdeckst dabei viel über Sprache und fremde Kultur.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf95" => new KategorieCardDefinition(
                id: new CardId('suf95'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Seniorenzentrum. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf96" => new KategorieCardDefinition(
                id: new CardId('suf96'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pause Investititonen',
                description: 'Das ständige Auf und Ab an den Finanzmärkten stresst dich – deshalb gönnst du dir eine Auszeit von deinen Investitionen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf97" => new KategorieCardDefinition(
                id: new CardId('suf97'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Abbestellung Börsenbericht',
                description: 'a du den Börsenbericht nicht mehr liest und die Zeitung mit dem Wirtschaftsressort abbestellt hast, genießt du nun mehr Freizeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf98" => new KategorieCardDefinition(
                id: new CardId('suf98'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Arbeit EineWeltLaden',
                description: 'Wöchentlich unterstützt du ehrenamtlich den EineWeltLaden, der fair gehandelte Produkte verkauft und so nachhaltigen Konsum fördert.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf99" => new KategorieCardDefinition(
                id: new CardId('suf99'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fundraisingaktion',
                description: 'Du setzt dich mit einer Fundraising-Aktion für die Nothilfe nach Naturkatastrophen ein – ein zeitintensives, aber wichtiges Engagement.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf100" => new KategorieCardDefinition(
                id: new CardId('suf100'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Du übernimmst einen Vorstandsposten im Tennisverein – eine verantwortungsvolle Aufgabe, die viel Zeit in Anspruch nimmt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf101" => new KategorieCardDefinition(
                id: new CardId('suf101'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ferienhaus in Italien',
                description: 'Du erwirbst ein wunderschönes, kleines Ferienhaus in Italien. Von nun an genießt du regelmäßig die italienische Natur, trinkst Espresso und isst viel Pasta.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-97000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf102" => new KategorieCardDefinition(
                id: new CardId('suf102'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schwimmbad',
                description: 'Du baust dir einen privaten Whirlpool als Ausgleich zum stressigen Alltag. Der Bau des Pools kostet zwar Zeit, doch die Auszeiten mit deinen Freunden werden dir guttun.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-78000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf103" => new KategorieCardDefinition(
                id: new CardId('suf103'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Charity-Event',
                description: '"Du planst ein gemeinnütziges Event, das gezielt Projekte fördert, die benachteiligte Männer in einer matriarchal geprägten Gesellschaft in Niger stärken."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-70000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf104" => new KategorieCardDefinition(
                id: new CardId('suf104'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Haushaltshilfe',
                description: 'Du beschäftigst eine Haushaltshilfe, die dich im Alltag unterstützt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf105" => new KategorieCardDefinition(
                id: new CardId('suf105'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Zweitwohnung Südsee',
                description: 'Du genießt die graue Jahreszeit in deiner Zweitwohnung auf den Südseeinseln. Die Wärme der Sonne gibt dir neue Energie, sodass dir deine Aufgaben leichter von der Hand gehen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-66000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf106" => new KategorieCardDefinition(
                id: new CardId('suf106'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Privatköchin',
                description: 'Du engagierst eine Privatköchin, die mit regionalen, saisonalen Zutaten schmackhafte und gesunde Mahlzeiten zubereitet, um bewussten Genuss und Nachhaltigkeit in euren Alltag zu bringen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-52000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf107" => new KategorieCardDefinition(
                id: new CardId('suf107'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ergänzung Weinsammlung',
                description: 'Du erweiterst deine Weinsammlung durch erlesene Weine aus Napa Valley in Kalifornien. Die stetig wachsende Vielfalt in deinem Weinkeller bereitet dir große Freude und belebt deinen Alltag.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-47000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf108" => new KategorieCardDefinition(
                id: new CardId('suf108'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pflegedienst',
                description: 'Du sorgst mit einem Pflegedienst für die notwendige Betreuung deiner Großeltern und nutzt die gemeinsame Zeit bewusst für Spaziergänge und entspannte Gespräche bei Kaffee und Kuchen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-45000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf109" => new KategorieCardDefinition(
                id: new CardId('suf109'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Aufbau Solarpark',
                description: 'Du errichtest am Stadtrand einen großen Solarpark, der dazu beiträgt, den CO2-Ausstoß deiner Stadt deutlich zu reduzieren und eine saubere Energieversorgung zu fördern.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-45000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf110" => new KategorieCardDefinition(
                id: new CardId('suf110'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Analyse Biorythmus',
                description: 'Du beauftragst eine detaillierte Analyse deines Biorhythmus und steigerst dadurch deine Arbeits',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-34500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf111" => new KategorieCardDefinition(
                id: new CardId('suf111'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Naturteich',
                description: 'Du lässt dir einen Naturteich in den Garten bauen, um nach der Arbeit gemütlich entspannen zu können. Jetzt erhältst du überraschenderweise viel mehr Besuch von deiner Nachbarschaft.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-52000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf112" => new KategorieCardDefinition(
                id: new CardId('suf112'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fitness',
                description: 'Du richtest dir ein individuell ausgestattetes Fitnesszimmer ein, das perfekt auf deine Bedürfnisse zugeschnitten ist und dir hilft, deine Ziele fit und motiviert zu verfolgen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-43000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf113" => new KategorieCardDefinition(
                id: new CardId('suf113'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Jahresurlaub',
                description: '"Du verbringst deinen Jahresurlaub damit, beim Wiederaufbau einer Schule nach einem Erdbeben tatkräftig zu helfen."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf114" => new KategorieCardDefinition(
                id: new CardId('suf114'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: '"Unterstützung nachhaltigeForschung"',
                description: 'Du unterstützt ein Forschungsprojekt, das Technologien entwickelt, um Kohlendioxid aus der Luft zu entfernen und in Sauerstoff umzuwandeln.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf115" => new KategorieCardDefinition(
                id: new CardId('suf115'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Umzug',
                description: 'Du beziehst eine ruhig gelegene Wohnung in der Nähe, die dank verbesserter Dämmung für mehr Ruhe sorgt. So schläfst du besser und fühlst dich tagsüber ausgeruhter.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf116" => new KategorieCardDefinition(
                id: new CardId('suf116'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Umzug',
                description: 'Du ziehst in eine zentrale, gut angebundene Wohnung, wodurch du auf lange Pendelstrecken verzichten kannst. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf117" => new KategorieCardDefinition(
                id: new CardId('suf117'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Garten- und Landschaftspflege',
                description: 'Du engagierst eine nachhaltige Garten- und Landschaftspflege, die deinen Garten naturnah pflegt. So kannst du deine Freizeit entspannter genießen und gleichzeitig die Umwelt schonen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-32000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf118" => new KategorieCardDefinition(
                id: new CardId('suf118'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: '"abgestimmte Nahrungs-ergänungsmittel"',
                description: 'Du schließt ein Abo für individuell auf deine Bedürfnisse abgestimmte Nahrungsergänzungsmittel ab, um deine Fitness optimal zu unterstützen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-27000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf119" => new KategorieCardDefinition(
                id: new CardId('suf119'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sponsoring',
                description: '"Du unterstützt als Sponsor eine Freizeit, die speziell für Kinder im Hospiz organisiert wird, um ihnen unvergessliche Erlebnisse zu ermöglichen."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf120" => new KategorieCardDefinition(
                id: new CardId('suf120'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Dauerkarte 1.Liga',
                description: 'Du sicherst dir eine VIP-Dauerkarte für deinen Lieblingsverein in der ersten Liga und bist dadurch bei jedem Heim- und Auswärtsspiel live dabei.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf121" => new KategorieCardDefinition(
                id: new CardId('suf121'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Zero-Waste-Modell',
                description: 'Du führst ein Zero-Waste-Modell für deine Stadt ein. Dafür musst du viele Expertinnen und finanzielle Mittel für den Aufbau des Modells beschaffen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf122" => new KategorieCardDefinition(
                id: new CardId('suf122'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: '"ökologisches Wirtschaftsnetzwerk"',
                description: 'Du gründest ein Netzwerk, in dem Unternehmen, Landwirte und Verbraucher eng zusammenarbeiten, um gemeinsam ökologische und nachhaltige Wirtschaftsweisen zu etablieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-25000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf123" => new KategorieCardDefinition(
                id: new CardId('suf123'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft',
                description: 'Du engagierst dich als Pate für drei Kinder in Moldawien und trägst aktiv zu ihrer Schulbildung und Entwicklung bei.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf124" => new KategorieCardDefinition(
                id: new CardId('suf124'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- & Steuerberaterin',
                description: '"Dank einer Empfehlung findest du eine erfahrene Finanz- und Steuerberaterin, die dich kompetent unterstützt."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf125" => new KategorieCardDefinition(
                id: new CardId('suf125'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende Tierheim',
                description: 'Regelmäßig spendest du bei deinen Einkäufen Tiernahrung an die umliegenden Tierheime.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf126" => new KategorieCardDefinition(
                id: new CardId('suf126'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Unterricht Waisenheim',
                description: '"Du verbringst sechs Monate in Afrika, um Kindern in einem Waisenheim Englischunterricht zu geben und ihnen damit Bildungschancen zu eröffnen."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf127" => new KategorieCardDefinition(
                id: new CardId('suf127'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Geschenke Obdachlose',
                description: '"Du bringst zu Ostern Geschenke zu den Obdachlosen in deiner Stadt, um ihnen eine kleine Freude zu bereiten."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf128" => new KategorieCardDefinition(
                id: new CardId('suf128'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Einkäufe',
                description: 'Statt selbst einkaufen zu gehen, nutzt du eine Drohnenlieferung mit nachhaltigen Verpackungen und kurzen Transportwegen. So schonst du die Umwelt und hast mehr Zeit für dich und deine Freunde.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf129" => new KategorieCardDefinition(
                id: new CardId('suf129'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- & Steuerberaterin',
                description: 'Du verlierst langsam den Überblick über deine Unterlagen der letzten Jahre – Zeit, eine Steuerberaterin zu engagieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf130" => new KategorieCardDefinition(
                id: new CardId('suf130'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf131" => new KategorieCardDefinition(
                id: new CardId('suf131'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Betreuung Buchhaltung',
                description: 'Du beauftragst ein Unternehmen mit der Betreuung deiner Buchhaltung und Terminorganisation – so gewinnst du mehr Zeit für die wirklich wichtigen Dinge.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf132" => new KategorieCardDefinition(
                id: new CardId('suf132'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf133" => new KategorieCardDefinition(
                id: new CardId('suf133'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende wohltätiger Zweck',
                description: 'Du unterstützt mit einer einmaligen Spende ein gemeinnütziges Projekt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf134" => new KategorieCardDefinition(
                id: new CardId('suf134'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vermögensberatung',
                description: 'Du engagierst eine Vermögensberaterin, die sich professionell um deine Investitionsanlagen kümmert und deine Finanzen optimal betreut.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-16000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf135" => new KategorieCardDefinition(
                id: new CardId('suf135'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'persönliche Assistentin',
                description: 'Du engagierst eine persönliche Assistentin, die dich bei deinen beruflichen Tätigkeiten unterstützt und dir den Arbeitsalltag erleichtert.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf136" => new KategorieCardDefinition(
                id: new CardId('suf136'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spazieren mit Hunden',
                description: 'Du hilfst im örtlichen Tierheim mit und gehst regelmäßig dreimal pro Woche mit den Hunden Gassi – ein Gewinn für Mensch und Tier.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf137" => new KategorieCardDefinition(
                id: new CardId('suf137'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendenmarathon',
                description: 'Du engagierst dich bei einem Spendenmarathon für krebskranke Kinder und musst viel Zeit in die Suche nach Sponsoren investieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf138" => new KategorieCardDefinition(
                id: new CardId('suf138'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Besuch Pflegeheim',
                description: 'Du gehst in eine Pflegeeinrichtung und organisierst für die Bewohnenden einen geselligen Brettspielabend, um gemeinsam Freude zu teilen und Abwechslung in den Alltag zu bringen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf139" => new KategorieCardDefinition(
                id: new CardId('suf139'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich in einem Haus für Menschen mit geistiger Behinderung. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf140" => new KategorieCardDefinition(
                id: new CardId('suf140'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Betreuung Gymnasium',
                description: 'Du setzt dich in einer Einrichtung für Menschen mit geistiger Behinderung ein und unterstützt die Bewohnenden aktiv im Alltag sowie bei Freizeitaktivitäten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf141" => new KategorieCardDefinition(
                id: new CardId('suf141'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf142" => new KategorieCardDefinition(
                id: new CardId('suf142'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Training Jugendmannschaft',
                description: 'Du trainierst jetzt die Jugendmannschaft deines Fußballvereins. Seitdem steckst du deine ganze Energie in den Nachwuchs – und nicht mehr in Investitionen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf143" => new KategorieCardDefinition(
                id: new CardId('suf143'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Treffen Mitbewohnenden',
                description: 'Du verbringst regelmäßig gesellige Abende mit deinen neuen Mitbewohnenden aus dem Iran, genießt gemeinsam landestypische Speisen und lernst viel über dir bisher unbekannten Kultur.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf144" => new KategorieCardDefinition(
                id: new CardId('suf144'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendenmarathon',
                description: 'Du engagierst dich bei einem Spendenmarathon für krebskranke Kinder und musst viel Zeit in die Suche nach Sponsoren investieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf145" => new KategorieCardDefinition(
                id: new CardId('suf145'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Bergwelt',
                description: 'Du verbringst einige Wochen in der abgelegenen und bezaubernden Bergwelt, um Abstand vom Alltag zu gewinnen und wieder neue Kraft zu schöpfen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf146" => new KategorieCardDefinition(
                id: new CardId('suf146'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Abbestellung Börsenbericht',
                description: 'Du gewinnst mehr freie Zeit, da du den Börsenbericht nicht mehr verfolgst und dein Abonnement der Zeitung mit dem exzellenten Wirtschaftsressort gekündigt hast.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf147" => new KategorieCardDefinition(
                id: new CardId('suf147'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pause Investititonen',
                description: 'Du entscheidest dich, eine Auszeit von deinen Investitionen zu nehmen, um dich nicht länger von den Schwankungen der Finanzmärkte stressen zu lassen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf148" => new KategorieCardDefinition(
                id: new CardId('suf148'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Party',
                description: 'Du organisierst eine große Party für all deine Freunde und Unterstützer, um dich gebührend zu bedanken. Die Vorbereitung nimmt viel Zeit in Anspruch.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf149" => new KategorieCardDefinition(
                id: new CardId('suf149'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Arbeit EineWeltLaden',
                description: 'Wöchentlich unterstützt du ehrenamtlich den EineWeltLaden, der fair gehandelte Produkte verkauft und so nachhaltigen Konsum fördert.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf150" => new KategorieCardDefinition(
                id: new CardId('suf150'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fundraisingaktion',
                description: 'Du setzt dich für die Nothilfe nach Naturkatastrophen ein, indem du eine Fundraisingaktion organisierst, die viel Zeit beansprucht.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf151" => new KategorieCardDefinition(
                id: new CardId('suf151'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Du übernimmst einen Vorstandsposten im Tennisverein – eine verantwortungsvolle Aufgabe, die viel Zeit in Anspruch nimmt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf152" => new KategorieCardDefinition(
                id: new CardId('suf152'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Du setzt einen Flyer auf, der über demokratische Werte informiert, und investierst eigenes Geld in den Druck.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf153" => new KategorieCardDefinition(
                id: new CardId('suf153'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Du setzt einen Flyer auf, der über demokratische Werte informiert, und investierst eigenes Geld in den Druck.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1300),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "j1" => new JobCardDefinition(
                id: new CardId('j1'),
                title: 'freiwilliges Praktikum',
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Duales Studium',
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn Du einen Job hast, kannst Du pro Jahr einen Zeitstein weniger setzen.',
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
            "e1" => new EreignisCardDefinition(
                id: new CardId('e1'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Teilnahme Coaching-Seminaren',
                description: 'Glückwunsch! Deine Teilnahme an Coaching-Seminaren zahlt sich aus: Du gewinnst bei einem Wettbewerb für junge Führungskräfte den ersten Platz und erhältst eine Finanzspritze für dein erstes Start-up.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e2" => new EreignisCardDefinition(
                id: new CardId('e2'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Herzlichen Glückwunsch! Deine Bewerbung für die Auszeichnung besonderer Prüfungsleistungen war erfolgreich.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e3" => new EreignisCardDefinition(
                id: new CardId('e3'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Neue Liebe',
                description: 'Du bist verliebt und vernachlässigst dadurch deine (Lern-)Pflichten. Alles wieder aufzuholen kostet viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e4" => new EreignisCardDefinition(
                id: new CardId('e4'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Work-Life-Balance',
                description: 'Du hast die optimale Mitte gefunden. Es eröffnen sich zahlreiche neue Möglichkeiten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e5" => new EreignisCardDefinition(
                id: new CardId('e5'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt, und es kommt zu Kurzarbeit. Dein Jahresgehalt (brutto) passt sich der Kurzarbeit an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e6" => new EreignisCardDefinition(
                id: new CardId('e6'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert – dein Jahresgehalt (brutto) wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:120,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e7" => new EreignisCardDefinition(
                id: new CardId('e7'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Lohnerhöhung',
                description: 'Die Gehaltsverhandlungen mit deinen Vorgesetzten liefen sehr gut und du erhälst für dieses Jahr mehr Jahresgehalt (brutto). ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:120,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e8" => new EreignisCardDefinition(
                id: new CardId('e8'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beziehungskrise',
                description: 'Du streitest dich nur noch mit deiner Partnerin und findest deshalb keine Zeit für die Hausarbeit. Den entstandenen Rückstand aufzuholen, kostet viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('e142'),
                gewichtung: 4,
            ),
            "e9" => new EreignisCardDefinition(
                id: new CardId('e9'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Neue Wohnung',
                description: 'Du ziehst um. Aufgrund des Umzugstresses vernachlässigst du deine anderen Verpflichtungen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e10" => new EreignisCardDefinition(
                id: new CardId('e10'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stress',
                description: 'Der Druck setzt dir zu, und du schläfst nicht genug. Deshalb nimmst du dir eine Auszeit. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e11" => new EreignisCardDefinition(
                id: new CardId('e11'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kündigung',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust kündigst du unüberlegt deinen Job und erhältst dieses Jahr kein Einkommen mehr.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e12" => new EreignisCardDefinition(
                id: new CardId('e12'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Dank deiner erfolgreichen Studienleistungen stehen dir spezialisierte Weiterbildungen offen. Du nutzt diese Möglichkeit, um dich gezielt weiterzubilden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e13" => new EreignisCardDefinition(
                id: new CardId('e13'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Dein Ausbildungsbetrieb empfiehlt dich für die Auszeichnung „Junge Talente“. Kurz darauf wirst du informiert, dass du die Auszeichnung erhalten hast.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(500),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e14" => new EreignisCardDefinition(
                id: new CardId('e14'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Deine Ausbildungsarbeit wurde mit einer überregionalen Auszeichnung bedacht.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1500),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e15" => new EreignisCardDefinition(
                id: new CardId('e15'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Für deine Seminararbeit erhältst du einen Fachpreis, der dir in deinem Bereich besondere Sichtbarkeit verschafft.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e16" => new EreignisCardDefinition(
                id: new CardId('e16'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Ein während deines Studiums gestartetes Projekt setzt sich nach dem Abschluss fort und erhält eine Förderauszeichnung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1500),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e17" => new EreignisCardDefinition(
                id: new CardId('e17'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auslandsstipendium',
                description: 'Aufgrund deiner guten Noten im Studium qualifizierst du dich für ein Auslandsstipendium und kannst so berufliche Entwicklung mit internationaler Erfahrung verbinden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(3000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e18" => new EreignisCardDefinition(
                id: new CardId('e18'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stipendium',
                description: 'Deine sehr guten Leistungen im Studium werden mit einem Stipendium honoriert, das dich finanziell entlastet und dein Profil weiter aufwertet.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(3000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e19" => new EreignisCardDefinition(
                id: new CardId('e19'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du entscheidest dich, dich privat weiterzubilden und startest eine Abendschule. Damit du mehr Zeit zum Lernen hast, reduzierst du für dieses Jahr deine Arbeitszeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e20" => new EreignisCardDefinition(
                id: new CardId('e20'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt, und es kommt zu Kurzarbeit. Dein Jahresgehalt (brutto) passt sich der Kurzarbeit an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:60,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e21" => new EreignisCardDefinition(
                id: new CardId('e21'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Um dich fachlich zu spezialisieren, machst du eine mehrmonatige Weiterbildung und reduzierst dafür dieses Jahr deine Arbeitszeit. Dein Jahresgehalt (brutto) wird entsprechend gekürzt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:50,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e22" => new EreignisCardDefinition(
                id: new CardId('e22'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Prämie',
                description: 'Deine Vorgesetzten sind stolz auf dich. Du hast dich in den letzten Monaten enorm weiterentwickelt. Aufgrund deiner starken Leistungen steigerte sich der Umsatz. Dies wird mit einer Prämie belohnt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(3000),
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:50,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e23" => new EreignisCardDefinition(
                id: new CardId('e23'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weihnachten',
                description: '"Weihnachten steht vor der Tür. Deine Teamleitung überrascht dich mit einem extra großen Weihnachtsgeld, da das Geschäftsjahr besonders gut ausfiel."',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e24" => new EreignisCardDefinition(
                id: new CardId('e24'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du entscheidest dich für eine achtmonatige berufsbegleitende Weiterbildung und reduzierst dafür deine Arbeitszeit. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e25" => new EreignisCardDefinition(
                id: new CardId('e25'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Leadership-Seminare',
                description: 'Deine Teamleitung erkennt dein Potenzial. Zweimal im Monat besuchst du ein Leadership-Seminar und bekommst dein eigenes Team, um das Gelernte umzusetzen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:130,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e26" => new EreignisCardDefinition(
                id: new CardId('e26'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: '"Mit Vitamin B die Karriere ankurbeln"',
                description: 'Dein Onkel lässt seine Beziehungen spielen, und du erhältst eine Beförderung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e27" => new EreignisCardDefinition(
                id: new CardId('e27'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Die wirtschaftliche Lage ist angespannt und und es kommt zu zahlreichen Entlassungen. Auch du bist betroffen und wirst entlassen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e28" => new EreignisCardDefinition(
                id: new CardId('e28'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Trennung',
                description: 'Deine Partnerin beendet die Beziehung mit dir. Du fällst tief in ein Loch. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e29" => new EreignisCardDefinition(
                id: new CardId('e29'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Betriebliche Weihnachtsfeier',
                description: 'Bei einer betrieblichen Weihnachtsfeier hast du etwas zu tief ins Glas geschaut und streng geheime interne Beschlüsse weitergegeben. Das hat fatale Folgen und kostet dich deinen Job.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +-1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e30" => new EreignisCardDefinition(
                id: new CardId('e30'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Mentoring-Programm',
                description: 'Du wirst in ein Mentoring-Programm aufgenommen, in dem erfahrene Fachkräfte dich bei deiner weiteren beruflichen Entwicklung unterstützen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e31" => new EreignisCardDefinition(
                id: new CardId('e31'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fachverband',
                description: 'Du wirst Mitglied in einem Fachverband, der junge Fachkräfte unterstützt und dir Zugang zu exklusiven Job- und Weiterbildungsangeboten ermöglicht.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e32" => new EreignisCardDefinition(
                id: new CardId('e32'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fachtagung',
                description: 'Du erhältst eine Einladung zur Teilnahme an einer Fachtagung, bei der du dich mit anderen erfolgreichen Auszubildenden vernetzen kannst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e33" => new EreignisCardDefinition(
                id: new CardId('e33'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Nachwuchsforum',
                description: '"Du erhältst eine Einladung zur Teilnahme an einem speziellen Nachwuchsforum für Fachkräfte in deinem Ausbildungsberuf."',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e34" => new EreignisCardDefinition(
                id: new CardId('e34'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Talentförderprogramm',
                description: 'Du wirst in ein hochkarätiges Talentförderprogramm aufgenommen und knüpfst dabei wertvolle Kontakte für deine zukünftige Karriere.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e35" => new EreignisCardDefinition(
                id: new CardId('e35'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Konferenz',
                description: 'Du nimmst an der Young Professional-Konferenz teil, präsentierst dort deine Expertise und beeindruckst Arbeitgeber unmittelbar im direkten Austausch.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e36" => new EreignisCardDefinition(
                id: new CardId('e36'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Alumni-Netzwerk',
                description: 'Du wirst in das Alumni-Netzwerk deiner Universität aufgenommen. Dadurch profitierst du auch zukünftig von exklusiven Veranstaltungen und Karrieremöglichkeiten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e37" => new EreignisCardDefinition(
                id: new CardId('e37'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Elite-Internat',
                description: 'Du schickst dein Kind auf ein Elite-Internat, um die beruflichen Chancen zu verbessern. Gleichzeitig knüpfst du bei deinen Besuchen wertvolle Kontakte zu Eltern aus aller Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e38" => new EreignisCardDefinition(
                id: new CardId('e38'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Vertiefe deine betriebswirtschaftlichen Kenntnisse durch ein weiterführendes Studium.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e39" => new EreignisCardDefinition(
                id: new CardId('e39'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Streit Kollegium',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust nimmst du dir zunächst vier Wochen unbezahlten Urlaub.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3100),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e40" => new EreignisCardDefinition(
                id: new CardId('e40'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Workshop',
                description: 'Ein Unternehmen wird auf deine Forschung aufmerksam und lädt dich zu einem exklusiven Workshop zum Thema Innovation ein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e41" => new EreignisCardDefinition(
                id: new CardId('e41'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Forschungsakademie',
                description: 'Du wirst durch eine Forschungsakademie zusätzlich gefördert – mit Reisekosten, Mentoring oder Soft-Skill-Trainings.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(3000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e42" => new EreignisCardDefinition(
                id: new CardId('e42'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Dein Forschungsartikel erhält einen internationalen Preis – eine bedeutende Würdigung deiner wissenschaftlichen Arbeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e43" => new EreignisCardDefinition(
                id: new CardId('e43'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Lehrauftrag',
                description: 'Aufgrund deiner Promotion erhältst du einen speziellen Lehrauftrag an deiner Universität.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(6000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e44" => new EreignisCardDefinition(
                id: new CardId('e44'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Herzlichen Glückwunsch! Deine Bewerbung für eine Auszeichnung wegen herausragender Prüfungsleistungen wurde angenommen, und du bekommst nachträglich einen Teil deiner BAFöG-Ausgaben erstattet.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(8000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e45" => new EreignisCardDefinition(
                id: new CardId('e45'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du spürst, wie sehr dich dein Job mental belastet. Deshalb reduzierst du dieses Jahr deine Arbeitszeit, um Stress vorzubeugen. Dein Jahresgehalt (brutto) wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e46" => new EreignisCardDefinition(
                id: new CardId('e46'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du hast eine kreative Idee, die du endlich umsetzen willst. Um daran zu arbeiten, reduzierst du dieses Jahr deine Arbeitszeit. Dein Jahresgehalt (brutto) wird entsprechend gekürzt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e47" => new EreignisCardDefinition(
                id: new CardId('e47'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt, und es kommt zu Kurzarbeit. Dein Jahresgehalt (brutto) passt sich der Kurzarbeit an.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e48" => new EreignisCardDefinition(
                id: new CardId('e48'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du planst deine Selbstständigkeit und brauchst dafür mehr Zeit. Deshalb reduzierst du in diesem Jahr deine Arbeitszeit und baust dein Unternehmen bei reduziertem Jahresgehalt (brutto) auf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:60,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e49" => new EreignisCardDefinition(
                id: new CardId('e49'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt, und es kommt zu Kurzarbeit. Dein Jahresgehalt (brutto) passt sich der Kurzarbeit an.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:60,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e50" => new EreignisCardDefinition(
                id: new CardId('e50'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du planst eine berufliche Neuorientierung und besuchst dafür eine Umschulung. Um genug Zeit dafür zu haben, reduzierst du deine Arbeitszeit. Dein Jahresgehalt (brutto) wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:50,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e51" => new EreignisCardDefinition(
                id: new CardId('e51'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Prämie',
                description: 'Deine Vorgesetzten sind stolz auf dich. Du hast dich in den letzten Monaten enorm weiterentwickelt. Aufgrund deiner starken Leistungen steigerte sich der Umsatz. Dies wird mit einer Prämie belohnt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5000),
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:50,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e52" => new EreignisCardDefinition(
                id: new CardId('e52'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du machst deinen Job hervorragend und erhältst eine unerwartete Beförderung für dieses Jahr. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:110,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e53" => new EreignisCardDefinition(
                id: new CardId('e53'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Ehrenamtliches Engagement',
                description: 'Dein großes Engagement in der Obdachlosenhilfe begeistert den Bürgermeister. Deshalb schlägt er dich für ein Stipendienprogramm vor. Du wirst ausgewählt und erhältst finanzielle Unterstützung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(12000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e54" => new EreignisCardDefinition(
                id: new CardId('e54'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert – dein Jahresgehalt (brutto) passt sich entsprechend an. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:120,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e55" => new EreignisCardDefinition(
                id: new CardId('e55'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du entscheidest dich für eine achtmonatige berufsbegleitende Weiterbildung und reduzierst dafür deine Arbeitszeit. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e56" => new EreignisCardDefinition(
                id: new CardId('e56'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kündigung',
                description: 'Du hast dich mit deinem Team zerstritten. Eine Zusammenarbeit ist nicht mehr möglich. Du kündigst und bekommst ab diesem Jahr kein Einkommen mehr.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e57" => new EreignisCardDefinition(
                id: new CardId('e57'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Work-Life-Balance ',
                description: 'Du hast die optimale Mitte gefunden. Es eröffnen sich zahlreiche neue Möglichkeiten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e58" => new EreignisCardDefinition(
                id: new CardId('e58'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stress',
                description: 'Der Druck setzt dir zu, und du schläfst nicht genug. Deshalb nimmst du dir eine Auszeit. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e59" => new EreignisCardDefinition(
                id: new CardId('e59'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Netzwerk',
                description: 'Bei einem Netzwerktreffen für junge Absolventen baust du dein berufliches Netzwerk aus und knüpfst wertvolle neue Kontakte.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j3'),
                gewichtung: 4,
            ),
            "e60" => new EreignisCardDefinition(
                id: new CardId('e60'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Talentscout',
                description: 'Schon seit einiger Zeit hast du die Aufmerksamkeit eines Talentscouts auf dich gezogen. Nun erhältst du die einmalige Chance, dich in einem Profifußballclub zu beweisen..',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf16'),
                gewichtung: 4,
            ),
            "e61" => new EreignisCardDefinition(
                id: new CardId('e61'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'erkranktes Familienmitglied',
                description: 'In deiner Familie erkrankt eine dir nahestehende Person schwer. Du kümmerst dich um eine geeignete Behandlung und kannst deshalb an einer wichtigen Fortbildung nicht teilnehmen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e62" => new EreignisCardDefinition(
                id: new CardId('e62'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Abschalten smarte Endgeräte',
                description: 'Du schaltest ab 18 Uhr deine smarten Geräte aus, um dich zu konzentrieren. Dadurch verpasst du Investitionschancen und kannst eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e63" => new EreignisCardDefinition(
                id: new CardId('e63'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Die wirtschaftliche Lage ist angespannt und und es kommt zu zahlreichen Entlassungen. Auch du bist betroffen und wirst entlassen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e64" => new EreignisCardDefinition(
                id: new CardId('e64'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Betriebliche Weihnachtsfeier',
                description: 'Bei einer betrieblichen Weihnachtsfeier hast du etwas zu tief ins Glas geschaut und streng geheime interne Beschlüsse weitergegeben. Das hat fatale Folgen und kostet dich deinen Job.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +-1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e65" => new EreignisCardDefinition(
                id: new CardId('e65'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Publikation',
                description: 'Deine Forschung wird in einer angesehenen Fachzeitschrift veröffentlicht – ein wichtiger Meilenstein in deiner wissenschaftlichen Karriere.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e66" => new EreignisCardDefinition(
                id: new CardId('e66'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Forschungskongress',
                description: 'Du wirst zu einem internationalen Forschungskongress eingeladen, um deine Forschungsergebnisse zu präsentieren und dich mit führenden Wissenschaftlerinnen zu vernetzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e67" => new EreignisCardDefinition(
                id: new CardId('e67'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Alumni-Netzwerk',
                description: 'Für deine Promotion wirst du in ein exklusives Fördernetzwerk aufgenommen, das dir langfristig Zugang zu Chancen in Wissenschaft und Praxis eröffnet.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e68" => new EreignisCardDefinition(
                id: new CardId('e68'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Netzwerk',
                description: 'Bei einem Netzwerktreffen für junge Absolventen baust du dein berufliches Netzwerk aus und knüpfst wertvolle neue Kontakte.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e69" => new EreignisCardDefinition(
                id: new CardId('e69'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Elite-Internat',
                description: 'Du ermöglichst deinem Kind den Besuch einem privaten Elite-Internat und profitierst selbst: Deine Besuche vor Ort erweitern auch deinen eigenen Horizont.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e70" => new EreignisCardDefinition(
                id: new CardId('e70'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Streit Kollegium',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust nimmst du dir zunächst vier Wochen unbezahlten Urlaub.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9800),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e71" => new EreignisCardDefinition(
                id: new CardId('e71'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Forschungsnetzwerk',
                description: 'Du wirst in das Forschungsnetzwerk eines Exzellenzclusters aufgenommen und profitierst von der Unterstützung durch Drittmittel sowie vom interdisziplinären Austausch.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(3500),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j75'),
                gewichtung: 4,
            ),
            "e72" => new EreignisCardDefinition(
                id: new CardId('e72'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Die Fakultät zeichnet deine Publikationen mit einem Forschungspreis für herausragende wissenschaftliche Leistungen aus.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(11000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j75'),
                gewichtung: 4,
            ),
            "e73" => new EreignisCardDefinition(
                id: new CardId('e73'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stipendium',
                description: 'Du erhältst ein Habilitations-Stipendium, um deine Forschung zu vertiefen und neue Projekte zu initiieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(22000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j75'),
                gewichtung: 4,
            ),
            "e74" => new EreignisCardDefinition(
                id: new CardId('e74'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Zur Vorbereitung auf eine Führungsposition nimmst du an einem internen Entwicklungsprogramm teil und reduzierst dafür in diesem Jahr deine Arbeitszeit. Dein Jahresgehalt (brutto) wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e75" => new EreignisCardDefinition(
                id: new CardId('e75'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt, und es kommt zu Kurzarbeit. Dein Jahresgehalt (brutto) passt sich der Kurzarbeit an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e76" => new EreignisCardDefinition(
                id: new CardId('e76'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Prämie',
                description: 'Deine Vorgesetzten sind stolz auf dich. Du hast dich in den letzten Monaten enorm weiterentwickelt. Aufgrund deiner starken Leistungen steigerte sich der Umsatz. Dies wird mit einer Prämie belohnt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:50,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e77" => new EreignisCardDefinition(
                id: new CardId('e77'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du machst deinen Job hervorragend und erhältst eine unerwartete Beförderung für dieses Jahr. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:130,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e78" => new EreignisCardDefinition(
                id: new CardId('e78'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert und dein Jahregsgehalt (brutto) steigert sich entsprechend für dieses Jahr.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:120,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e79" => new EreignisCardDefinition(
                id: new CardId('e79'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Ehrenamtliches Engagement',
                description: 'Dein großes Engagement in dem Flüchtlingsheim begeistert den Bürgermeister. Deshalb schlägt er dich für ein Stipendienprogramm vor. Du wirst ausgewählt und erhältst finanzielle Unterstützung.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(8000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e80" => new EreignisCardDefinition(
                id: new CardId('e80'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du entscheidest dich für eine achtmonatige berufsbegleitende Weiterbildung und reduzierst dafür deine Arbeitszeit. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e81" => new EreignisCardDefinition(
                id: new CardId('e81'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stress',
                description: 'Der Druck setzt dir zu, und du schläfst nicht genug. Deshalb nimmst du dir eine Auszeit. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e82" => new EreignisCardDefinition(
                id: new CardId('e82'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Work-Life-Balance',
                description: 'Du hast die optimale Mitte gefunden. Es eröffnen sich zahlreiche neue Möglichkeiten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e83" => new EreignisCardDefinition(
                id: new CardId('e83'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kündigung',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust kündigst du unüberlegt deinen Job und erhältst dieses Jahr kein Einkommen mehr.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e84" => new EreignisCardDefinition(
                id: new CardId('e84'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Abschalten smarte Endgeräte',
                description: 'Du schaltest ab 18 Uhr deine smarten Geräte aus, um dich zu konzentrieren. Dadurch verpasst du Investitionschancen und kannst eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e85" => new EreignisCardDefinition(
                id: new CardId('e85'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Deine Heimatuniversität ehrt dich für deine außergewöhnlichen Verdienste und dein langjähriges Engagement mit der Verleihung der Ehrendoktorwürde.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e86" => new EreignisCardDefinition(
                id: new CardId('e86'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kinderbetreuung',
                description: 'Dein Kind hat Schwierigkeiten in der Schule und braucht mehr Aufmerksamkeit von dir. Deshalb verbringst du deine Freizeit nun vermehrt mit deinem Nachwuchs und bildest dich weniger weiter.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e87" => new EreignisCardDefinition(
                id: new CardId('e87'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Die wirtschaftliche Lage ist angespannt und und es kommt zu zahlreichen Entlassungen. Auch du bist betroffen und wirst entlassen. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e88" => new EreignisCardDefinition(
                id: new CardId('e88'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Betriebliche Weihnachtsfeier',
                description: 'Bei einer betrieblichen Weihnachtsfeier hast du etwas zu tief ins Glas geschaut und streng geheime interne Beschlüsse weitergegeben. Das hat fatale Folgen und kostet dich deinen Job.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +-1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e89" => new EreignisCardDefinition(
                id: new CardId('e89'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fachtagung',
                description: 'Du wirst zu einer internationalen Fachtagung eingeladen – ein Förderprogramm übernimmt deine Reise- und Aufenthaltskosten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j75'),
                gewichtung: 4,
            ),
            "e90" => new EreignisCardDefinition(
                id: new CardId('e90'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Gastprofessur',
                description: 'Du erhältst eine Einladung zu einer Gastprofessur an einer renommierten ausländischen Universität.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(45000),
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j75'),
                gewichtung: 4,
            ),
            "e91" => new EreignisCardDefinition(
                id: new CardId('e91'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fachtagung',
                description: 'Deine Habilitation bringt dir Einladungen zu hochrangigen Fachtagungen, etwa in Akkreditierungskommissionen oder wissenschaftlichen Beiräten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j75'),
                gewichtung: 4,
            ),
            "e92" => new EreignisCardDefinition(
                id: new CardId('e92'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du bekommst eine chronische Sehnenscheidenentzündung und kannst deinen Beruf nicht mehr ausüben. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e93" => new EreignisCardDefinition(
                id: new CardId('e93'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du hast wegene eines Bandscheibenvorfalls chronische Rückenprobleme und kannst nicht mehr arbeiten. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e94" => new EreignisCardDefinition(
                id: new CardId('e94'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du bekommst chronische Gelenkschmerzen und kannst deinen Beruf nicht mehr ausüben. Mit Berufsunfähigkeitsversicherung erhältst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e95" => new EreignisCardDefinition(
                id: new CardId('e95'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Nach einer Krebserkrankung bist du dauerhaft geschwächt und kannst deinem Beruf nicht mehr nachgehen. Mit Berufsunfähigkeitsversicherung bekommst du weiterhin dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job starten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e96" => new EreignisCardDefinition(
                id: new CardId('e96'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Durch einen schweren Schlaganfall kannst du deinen Beruf nicht mehr ausüben. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e97" => new EreignisCardDefinition(
                id: new CardId('e97'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du leidest unter schweren Depressionen und kannst für dieses Jahr nicht mehr arbeiten. Mit einer Berufsunfähigkeitsversicherung erhältst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e98" => new EreignisCardDefinition(
                id: new CardId('e98'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Ein Schlaganfall lähmt dich halbseitig, trotz Reha bleibt die Lähmung. Du kannst nicht mehr arbeiten, bekommst mit Berufsunfähigkeitsversicherung aber weiter dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e99" => new EreignisCardDefinition(
                id: new CardId('e99'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: '"Eine chronische Lungenerkrankung zwingt dich, deinen Beruf aufzugeben. Mit Berufsunfähigkeitsversicherung wird dein Jahresgehalt (brutto) weiterhin gezahlt. Ab dem neuen Jahr kannst du einen neuen Job antreten."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e100" => new EreignisCardDefinition(
                id: new CardId('e100'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: '"Nach einem schweren Autounfall bist du geistig eingeschränkt und kannst deinem Beruf nicht mehr nachgehen. Deine Berufsunfähigkeitsversicherung sichert dir trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e101" => new EreignisCardDefinition(
                id: new CardId('e101'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du hast eine schwere Herzkrankheit, die dich arbeitsunfähig macht. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e102" => new EreignisCardDefinition(
                id: new CardId('e102'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du erleidest bei einem Unfall eine Querschnittslähmung ab der Taille und kannst deinen Beruf nicht mehr ausüben. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e103" => new EreignisCardDefinition(
                id: new CardId('e103'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Beim Sport verletzt du dich. Mehrere Wirbelbrüche führen dazu, dass du deinen Beruf nicht mehr ausüben kannst. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e105" => new EreignisCardDefinition(
                id: new CardId('e105'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du erleidest einen schweren Burnout und kannst deinen Beruf nicht mehr ausüben. Mit Berufsunfähigkeitsversicherung erhältst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e106" => new EreignisCardDefinition(
                id: new CardId('e106'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Eine fortschreitende neurologische Erkrankung wie Parkinson macht eine weitere Berufsausübung unmöglich. Mit Berufsunfähigkeitsversicherung erhälst du trotzdem dein Jahresgehalt (brutto). Ab dem neuen Jahr kannst du einen neuen Job beginnen. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e107" => new EreignisCardDefinition(
                id: new CardId('e107'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du verschüttest Saft auf dem Laptop einer Kollegin, der dadurch unbrauchbar wird. Mit einer abgeschlossenen Haftpflichtversicherung werden die Kosten ersetzt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e108" => new EreignisCardDefinition(
                id: new CardId('e108'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Beim Präsentieren fällt dir ein Beamer vom Tisch, der dabei kaputtgeht und ersetzt werden muss. Mit einer abgeschlossenen Haftpflichtversicherung wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-900),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e109" => new EreignisCardDefinition(
                id: new CardId('e109'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du hast dir für ein Gruppenprojekt ein Tablet ausgeliehen und lässt es versehentlich fallen. Der Bildschirm ist beschädigt. Falls du eine Haftpflichtversicherung hast, übernimmt diese den Schaden.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e110" => new EreignisCardDefinition(
                id: new CardId('e110'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du stößt versehentlich einen Monitor in einem Seminarraum um, wodurch das Display springt und der Monitor unbrauchbar wird. Mit einer abgeschlossenen Haftpflichtversicherung wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-250),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e111" => new EreignisCardDefinition(
                id: new CardId('e111'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Fußballspiel trittst du versehentlich einem Mitspieler gegen das Knie. Mit einer abgeschlossenen Haftpflichtversicherung werden die Kosten für Personenschäden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1500),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf16'),
                gewichtung: 4,
            ),
            "e112" => new EreignisCardDefinition(
                id: new CardId('e112'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du hilfst beim Umzug und lässt einen Fernseher fallen. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-800),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e113" => new EreignisCardDefinition(
                id: new CardId('e113'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du wirfst deinen Laptop beim Aufräumen aus Versehen vom Tisch. Die Kosten musst du selbst tragen, denn die Haftpflichtversicherung deckt nur Schäden an Sachen, die dir nicht gehören.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e114" => new EreignisCardDefinition(
                id: new CardId('e114'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Einkaufen stößt du versehentlich gegen eine teure Vase, die daraufhin zu Bruch geht. Mit abgeschlossener Haftpflichtversicherung werden die entstandenen Sachschäden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e115" => new EreignisCardDefinition(
                id: new CardId('e115'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: '"Beim gemeinsamen Klettern verursacht du unabsichtlich einen Unfall, bei dem eine andere Person verletzt wird. Mit abgeschlossener Haftpflichtversicherung werden die entstandenen Personenschäden übernommen."',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-800),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e116" => new EreignisCardDefinition(
                id: new CardId('e116'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: '"Du lässt beim Spielen mit Freunden versehentlich deren Smartphone fallen und beschädigst es. Mit abgeschlossener Haftpflichtversicherung werden die entstandenen Sachschäden übernommen."',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-600),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e117" => new EreignisCardDefinition(
                id: new CardId('e117'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Bei einem Kundentermin stößt du aus Versehen eine teure Kamera vom Tisch. Der Laptop funktioniert nicht mehr. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2800),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e118" => new EreignisCardDefinition(
                id: new CardId('e118'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du schließt versehentlich deine Wasserflasche nicht richtig, und der Rucksack deiner Arbeitskollegin mit Laptop wird nass. Der Laptop funktioniert nicht mehr. Im Falle einer abgeschlossenen Haftpflichtversicherung wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e119" => new EreignisCardDefinition(
                id: new CardId('e119'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du verschüttest Wasser auf die Tastatur deines Kollegen. Die Tastatur funktioniert nicht mehr. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e120" => new EreignisCardDefinition(
                id: new CardId('e120'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du hast deine Waschmaschine falsch angeschlossen und das Parkett in der Mietwohnung damit ruiniert. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e121" => new EreignisCardDefinition(
                id: new CardId('e121'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: '"Beim Grillen auf dem Balkon deines Mietshauses entsteht Rußschaden an der Fassade.Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e122" => new EreignisCardDefinition(
                id: new CardId('e122'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Dein Kleinkind zerkratzt ein parkendes Auto mit einem Stein. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3800),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e123" => new EreignisCardDefinition(
                id: new CardId('e123'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du kippst ein Glas Rotwein auf dem Sofa eines Freundes um. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1500),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e124" => new EreignisCardDefinition(
                id: new CardId('e124'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: '"Beim Spielen auf dem Spielplatz verletzt dein Kind einen anderen spielenden Jungen. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für die Behandlung übernommen."',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2700),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e125" => new EreignisCardDefinition(
                id: new CardId('e125'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Sporttraining schießt du versehentlich einen Ball gegen die Fensterfront der Halle. Mit abgeschlossener Haftpflichtversicherung werden die Kosten übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e126" => new EreignisCardDefinition(
                id: new CardId('e126'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Inlineskaten kollidierst du unabsichtlich mit einem Fahrradfahrer, der verletzt wird. Mit abgeschlossener Haftpflichtversicherung werden die Kosten übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2200),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e127" => new EreignisCardDefinition(
                id: new CardId('e127'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Besuch im Museum stößt du versehentlich ein wertvolles Kunstobjekt um. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-60000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e128" => new EreignisCardDefinition(
                id: new CardId('e128'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du schließt den Geschirrspüler selbst an, ein Schlauch platzt, und Wasser läuft in die Wohnung darunter. Bei abgeschlossener Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-25000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e129" => new EreignisCardDefinition(
                id: new CardId('e129'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du lässt ein Handtuch über einer Stehlampe hängen – es fängt Feuer und beschädigt Teile des Hotelzimmers. Bei abgeschlossener Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-18000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e130" => new EreignisCardDefinition(
                id: new CardId('e130'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: '"Beim Volleyball beschädigst du versehentlich die Zahnprothese eines Mitspielers. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Ersatz übernommen."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf3'),
                gewichtung: 4,
            ),
            "e131" => new EreignisCardDefinition(
                id: new CardId('e131'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du verlierst den Schlüssel zum Bürogebäude und die Schließanlage muss ausgetauscht werden. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4500),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e132" => new EreignisCardDefinition(
                id: new CardId('e132'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Beim Aufbau der Firmenveranstaltung beschädigst du ein Lichtsystem. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e133" => new EreignisCardDefinition(
                id: new CardId('e133'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du lädst versehentlich Schadsoftware per USB-Stick auf einen Arbeitsrechner. Ein Teil des internen Netzwerks fällt aus. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e134" => new EreignisCardDefinition(
                id: new CardId('e134'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'kein Finanzbericht lesen',
                description: 'Du hast mehr Freizeit, weil du den Börsenbericht nicht mehr liest. Dafür kannst du eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e135" => new EreignisCardDefinition(
                id: new CardId('e135'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'KiTa',
                description: 'Du findest einen Platz in einer KiTa für dein Kind und gibst es dort in kompetente Betreuung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e136" => new EreignisCardDefinition(
                id: new CardId('e136'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'BabysitterIn',
                description: 'Du stellst eine Babysitterin ein, damit du mehr Freiraum für dich hast.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e137" => new EreignisCardDefinition(
                id: new CardId('e137'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du spürst, wie sehr dich dein Job mental belastet. Deshalb reduzierst du dieses Jahr deine Arbeitszeit, um Stress vorzubeugen. Dein Bruttogehalt wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:50,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e138" => new EreignisCardDefinition(
                id: new CardId('e138'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Weil dir Familie und Freundschaft am wichtigsten sind, findest du wenig Zeit für Investitionen und kannst eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e139" => new EreignisCardDefinition(
                id: new CardId('e139'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sabbatjahr',
                description: 'Für deine Weltreise nimmst du dir ein Sabbatjahr – auch wenn das bedeutet, dieses Jahr kein Gehalt zu bekommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e140" => new EreignisCardDefinition(
                id: new CardId('e140'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Weil dir Familie und Freundschaft am wichtigsten sind, findest du wenig Zeit für Investitionen und kannst eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e141" => new EreignisCardDefinition(
                id: new CardId('e141'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Weil dir Familie und Freundschaft am wichtigsten sind, findest du wenig Zeit für Investitionen und kannst eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e142" => new EreignisCardDefinition(
                id: new CardId('e142'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Hochzeit',
                description: 'Herzlichen Glückwunsch, du findest deine Partnerin fürs Leben. Euer besonderer Tag war ein großer Aufwand, aber mit deiner neu gegründeten Familie erlebst du großes Glück und erfüllte Zeiten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_NO_CHILD,
                ],
                gewichtung: 10,
            ),
            "e143" => new EreignisCardDefinition(
                id: new CardId('e143'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem Erasmus',
                description: 'Du verstehst dich so gut mit deinem Sprachtandem aus dem Erasmus-Programm, dass du beschließt, sein Heimatland zu besuchen. Dabei begegnest du vielen großartigen Menschen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1500),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf9'),
                gewichtung: 4,
            ),
            "e144" => new EreignisCardDefinition(
                id: new CardId('e144'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deiner Nachbarin beeinträchtigen dich erheblich, weshalb es zu einem Rechtsstreit kommt. Die daraus resultierenden Gerichtskosten musst du tragen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-800),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e145" => new EreignisCardDefinition(
                id: new CardId('e145'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Deine Informationsflyer zu demokratischen Werten kommen so gut an, dass du eine weitere Auflage drucken lässt. Die Druckkosten trägst du erneut selbst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf18'),
                gewichtung: 4,
            ),
            "e146" => new EreignisCardDefinition(
                id: new CardId('e146'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Dein Job hat dazu geführt, dass du deine Freundschaften vernachlässigt hast. Um das zu ändern, reduzierst du deine Arbeitszeit dieses Jahr  – dein Bruttogehalt sinkt entsprechend.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e147" => new EreignisCardDefinition(
                id: new CardId('e147'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Lottogewinn',
                description: '"Herzlichen Glückwunsch! Du hast den Jackpot gewonnen und kannst dich über eine beträchtliche Gewinnsumme freuen."',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e148" => new EreignisCardDefinition(
                id: new CardId('e148'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Eislaufen stürzt du und verletzt dir das Handgelenk, weshalb du eine Woche arbeitsunfähig bist. Mit einer privaten Unfallversicherung bekommst du in diesem Fall eine einmalige Invaliditätszahlung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(200),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e149" => new EreignisCardDefinition(
                id: new CardId('e149'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Heimwerken verletzt du dir die Hand, was dich dauerhaft einschränkt und zum Jobverlust führt. Im Falle einer abgeschlossenen privaten Unfallversicherung erhälst du einmalig Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(6000),
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e150" => new EreignisCardDefinition(
                id: new CardId('e150'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Tristan ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage:10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e151" => new EreignisCardDefinition(
                id: new CardId('e151'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Deine Tochter Alisa ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage:10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e152" => new EreignisCardDefinition(
                id: new CardId('e152'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kündigung',
                description: 'Du entscheidest dich, deinen Job zu kündigen und auf Reisen zu gehen, um dich neu zu orientieren. Damit verzichtest du jedoch auch auf dein Gehalt für dieses Jahr.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e153" => new EreignisCardDefinition(
                id: new CardId('e153'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geschenk',
                description: 'Deine beste Freundin hat eine Überraschung für dich vorbereitet: ein Wellness-Wochenende in Südtirol.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e154" => new EreignisCardDefinition(
                id: new CardId('e154'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer heftigen Influenza und liegst komplett flach. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e155" => new EreignisCardDefinition(
                id: new CardId('e155'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Gewinn ',
                description: 'Herzlichen Glückwunsch – du hast bei einer Verlosung ein E-Bike gewonnen! Damit kommst du künftig schneller zu deinen Terminen und kannst deine Freizeit noch entspannter genießen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e156" => new EreignisCardDefinition(
                id: new CardId('e156'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Soziales Engagement',
                description: 'Dein Chef schätzt dein soziales Engagement sehr. Er unterstützt deine sozialen Projekte von Herzen und gewährt dir Sonderurlaub für das kommende Sommercamp.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e157" => new EreignisCardDefinition(
                id: new CardId('e157'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer heftigen Influenza und liegst komplett flach. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e158" => new EreignisCardDefinition(
                id: new CardId('e158'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbau Überstunden',
                description: 'In den letzten Jahren hast du so fleißig gearbeitet, dass sich viele Überstunden angesammelt haben. Jetzt ist es an der Zeit, diese abzubauen. Du nimmst dir einen Monat frei und reist durch Asien.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e159" => new EreignisCardDefinition(
                id: new CardId('e159'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du hast dich mit deinem Team zerstritten und vereinbarst mit deiner Chefin einen Aufhebungsvertrag. Dadurch verlierst du deinen Job und bist vorerst arbeitslos.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e160" => new EreignisCardDefinition(
                id: new CardId('e160'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Burn-Out',
                description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Um dich wieder zu erholen, gehst du in eine Rehaklinik. Setze eine Runde aus.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e161" => new EreignisCardDefinition(
                id: new CardId('e161'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du bekommst eine Magen-Darm-Grippe und kannst für eine Weile nichts essen. Du pausierst, um dich zu erholen, bevor du wieder weitermachen kannst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e162" => new EreignisCardDefinition(
                id: new CardId('e162'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Streit mit der Familie ',
                description: 'An Weihnachten hast du dich mit deiner Schwester zerstritten. Nun musst du die Wogen wieder glätten. Das kostet dich Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e163" => new EreignisCardDefinition(
                id: new CardId('e163'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Dein Einsatz für den Schutz heimischer Bienenpopulation wird von dem deutschen Ehrenamtspreis ausgezeichnet.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e164" => new EreignisCardDefinition(
                id: new CardId('e164'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Weil sich leider niemand sonst für den Vorstandsposten im Tennisverein zur Verfügung stellt, übernimmst du das Amt für eine weitere Amtszeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf47'),
                gewichtung: 4,
            ),
            "e165" => new EreignisCardDefinition(
                id: new CardId('e165'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du wirst wegen unentschuldigtem Fehlen fristlos gekündigt und bekommst kein Gehalt mehr. Durch die Arbeitslosigkeit gewinnst du jedoch mehr freie Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e166" => new EreignisCardDefinition(
                id: new CardId('e166'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Herzlichen Glückwunsch zum Preis für Integration! Dein Projekt fördert nachhaltige Inklusion, indem es Jugendliche mit und ohne Beeinträchtigungen durch gemeinschaftlichen Sport zusammenbringt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e167" => new EreignisCardDefinition(
                id: new CardId('e167'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Besuch Internat',
                description: 'Dein Kind wünscht sich den Besuch eines privaten Sportinternats. Das verursacht hohe Ausgaben, verschafft dir aber auch wertvolle neue Kontakte zu anderen Eltern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e168" => new EreignisCardDefinition(
                id: new CardId('e168'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Nachhilfe',
                description: 'Um ihre schulischen Leistungen zu steigern, besucht dein Kind privaten Nachhilfeunterricht.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-28000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e169" => new EreignisCardDefinition(
                id: new CardId('e169'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Hochzeit',
                description: 'Herzlichen Glückwunsch, du findest deine Partnerin fürs Leben. Euer besonderer Tag war ein großer Aufwand, aber mit deiner neu gegründeten Familie erlebst du großes Glück und erfüllte Zeiten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-25000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_NO_CHILD,
                ],
                gewichtung: 10,
            ),
            "e170" => new EreignisCardDefinition(
                id: new CardId('e170'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Bauernhof-KiTa',
                description: 'In der Bauernhof-Kita ist wieder Platz. Dein Kind wird liebevoll von qualifiziertem Personal begleitet und erlebt naturnahes Lernen, das Umweltbewusstsein, Bewegung und nachhaltige Werte fördert.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9500),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e171" => new EreignisCardDefinition(
                id: new CardId('e171'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deiner Nachbarin beeinträchtigen dich erheblich, weshalb es zu einem Rechtsstreit kommt. Die daraus resultierenden Gerichtskosten musst du tragen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e172" => new EreignisCardDefinition(
                id: new CardId('e172'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Deine Informationsflyer zu demokratischen Werten kommen so gut an, dass du eine weitere Auflage drucken lässt. Die Druckkosten trägst du erneut selbst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf152'),
                gewichtung: 4,
            ),
            "e173" => new EreignisCardDefinition(
                id: new CardId('e173'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Sportklettern stürzt du und brichst dir den Arm mehrfach. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du eine einmalige Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(8500),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e174" => new EreignisCardDefinition(
                id: new CardId('e174'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Du stürzt beim Inlineskaten und hast einen komplizierten Bruch. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du eine einmalige Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(9000),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e175" => new EreignisCardDefinition(
                id: new CardId('e175'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Um deinen Alltag zu entschleunigen und mehr Zeit zum Kochen, für Freunde und für die Natur zu haben, arbeitest du dieses Jahr nicht in Vollzeit. Dein Bruttogehalt sinkt entsprechend.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:60,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e176" => new EreignisCardDefinition(
                id: new CardId('e176'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du verringerst dieses Jahr deine Arbeitszeit, um dich beim Aufbau eines Obdachlosenheims zu beteiligen. Dein Gehalt wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:90,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e177" => new EreignisCardDefinition(
                id: new CardId('e177'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Ein neues Tier zieht bei dir ein und braucht viel Aufmerksamkeit. Du reduzierst deine Arbeitszeit, dein Gehalt verringert sich entsprechend.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e178" => new EreignisCardDefinition(
                id: new CardId('e178'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kinderbetreuung',
                description: 'Wegen aggressivem Verhalten ist dein Kind vorübergehend aus dem Kindergarten ausgeschlossen. Du betreust es selbst und arbeitest weniger, dein Gehalt sinkt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:75,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e179" => new EreignisCardDefinition(
                id: new CardId('e179'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Du betreust parallel deine Kinder und deine Eltern. Deshalb verringerst du deine Arbeitszeit, was sich auch auf dein Gehalt auswirkt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e180" => new EreignisCardDefinition(
                id: new CardId('e180'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'persönliche Assistentin',
                description: 'Du stellst eine persönliche Assistentin ein, die dich nachhaltig bei deinen beruflichen Aufgaben unterstützt. Du investierst in diese Rolle, um Arbeit und Effizienz langfristig zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e181" => new EreignisCardDefinition(
                id: new CardId('e181'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Ein Familienmitglied benötigt mehr Unterstützung im Alltag. Du übernimmst einen Teil der Pflege und reduzierst deshalb deine Arbeitszeit. Dein Gehalt wird dementsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:60,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e182" => new EreignisCardDefinition(
                id: new CardId('e182'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Lottogewinn',
                description: 'Herzlichen Glückwunsch zum Lottogewinn! Du darfst dich über eine hohe Gewinnsumme freuen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(40000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e183" => new EreignisCardDefinition(
                id: new CardId('e183'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Du setzt dich für sozial benachteiligte Menschen mit Beeinträchtigung ein und unterstützt ihre Teilhabe am gesellschaftlichen Leben. Dafür bekommst du eine Auszeichnung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(50000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e184" => new EreignisCardDefinition(
                id: new CardId('e184'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Liam ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage:10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e185" => new EreignisCardDefinition(
                id: new CardId('e185'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Ali ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage:10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e186" => new EreignisCardDefinition(
                id: new CardId('e186'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Beziehungskrise',
                description: 'Du nimmst eine Paartherapie in Anspruch, um deine Beziehung zu retten – was bei den Obamas erfolgreich war, kann auch euch unterstützen. Allerdings bleibt dir dadurch vorerst weniger Zeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('e169'),
                gewichtung: 4,
            ),
            "e187" => new EreignisCardDefinition(
                id: new CardId('e187'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sucht',
                description: 'Du verbringst bei Treffen mit Freundinnen viel Zeit am Handy. Das stört sie und führt dazu, dass du immer mehr ausgegrenzt wirst. Die alten Freundschaften wiederherzustellen, braucht Zeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e188" => new EreignisCardDefinition(
                id: new CardId('e188'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Trennung',
                description: 'Deine Partnerin beendet die Beziehung mit dir. Du fällst tief in ein Loch. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('e142'),
                gewichtung: 1,
            ),
            "e189" => new EreignisCardDefinition(
                id: new CardId('e189'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Aufgrund deines Jobs hast du deine Freundschaften nicht gepflegt. Das musst du dringend ändern!',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e190" => new EreignisCardDefinition(
                id: new CardId('e190'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sabbatjahr',
                description: 'Für deine Weltreise nimmst du dir ein Sabbatjahr – auch wenn das bedeutet, dieses Jahr kein Gehalt zu bekommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:0,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e191" => new EreignisCardDefinition(
                id: new CardId('e191'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geschenk',
                description: 'Deine beste Freundin organisiert eine Überraschung und schenkt dir ein Wellness-Wochenende in Südtirol.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e192" => new EreignisCardDefinition(
                id: new CardId('e192'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Burn-Out',
                description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Um dich wieder zu erholen, gehst du in eine Rehaklinik. Setze eine Runde aus.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e193" => new EreignisCardDefinition(
                id: new CardId('e193'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburtstagsparty',
                description: 'Du organisierst eine Geburtstagsparty für Freunde und Familie. Das kostet dich viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e194" => new EreignisCardDefinition(
                id: new CardId('e194'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Streit mit der Familie ',
                description: 'An Weihnachten gab es Streit mit deiner Schwester. Jetzt solltest du versuchen, die Beziehung wieder zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e195" => new EreignisCardDefinition(
                id: new CardId('e195'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Gewinn',
                description: 'Herzlichen Glückwunsch! Du hast bei einer Verlosung einen E-Roller gewonnen. Damit bist du schneller bei Terminen und kannst deine Freizeit ausgiebig genießen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e196" => new EreignisCardDefinition(
                id: new CardId('e196'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer Herzmuskelentzündung und liegst komplett flach. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e197" => new EreignisCardDefinition(
                id: new CardId('e197'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kündigung',
                description: 'Du kündigst deinen Job, um auf Reisen neue Wege zu entdecken – dadurch entfällt aber auch dein Einkommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e198" => new EreignisCardDefinition(
                id: new CardId('e198'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbau Überstunden',
                description: 'Nach Jahren voller Überstunden gönnst du dir bewusst eine Auszeit und nutzt einen Monat für eine Reise durch Asien, um neue Energie zu tanken und deine Work-Life-Balance nachhaltig zu stärken.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e199" => new EreignisCardDefinition(
                id: new CardId('e199'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du wirst wegen unentschuldigtem Fehlen fristlos gekündigt und verlierst dadurch dein Einkommen. Deine Arbeitslosigkeit verschafft dir nun mehr freie Zeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e200" => new EreignisCardDefinition(
                id: new CardId('e200'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du hast dich mit deinem Team zerstritten und dich mit deiner Chefin auf einen Aufhebungsvertrag geeinigt. Damit verlierst du deinen aktuellen Job und bist zunächst arbeitslos.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e201" => new EreignisCardDefinition(
                id: new CardId('e201'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer Herzmuskelentzündung und liegst komplett flach. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e202" => new EreignisCardDefinition(
                id: new CardId('e202'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Dein Kind hat Windpocken. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 1,
            ),
            "e203" => new EreignisCardDefinition(
                id: new CardId('e203'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Dein Kind hat Läuse. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 1,
            ),
            "e204" => new EreignisCardDefinition(
                id: new CardId('e204'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Glückwunsch zum Preis für dein soziales Engagement! Durch deinen Einsatz im Flüchtlingszentrum hast du zahlreichen Menschen geholfen, sich rasch zu integrieren.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e205" => new EreignisCardDefinition(
                id: new CardId('e205'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'soziales Engagement',
                description: 'Dein Chef schätzt dein soziales Engagement sehr und unterstützt deine Projekte gerne. Für das nächste Sommercamp erhältst du sofort Sonderurlaub.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e206" => new EreignisCardDefinition(
                id: new CardId('e206'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Da sich niemand für deinen Vorstandsposten im Tennisverein findet, übernimmst du das Amt für eine weitere Periode.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf100'),
                gewichtung: 5,
            ),
            "e207" => new EreignisCardDefinition(
                id: new CardId('e207'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Besuch im Museum stößt du versehentlich ein wertvolles Kunstobjekt um. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-60000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e208" => new EreignisCardDefinition(
                id: new CardId('e208'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Besuch Internat',
                description: 'Dein Kind wünscht sich den Besuch eines privaten Sportinternats. Das verursacht hohe Ausgaben, verschafft dir aber auch wertvolle neue Kontakte zu anderen Eltern.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e209" => new EreignisCardDefinition(
                id: new CardId('e209'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Hochzeit',
                description: 'Herzlichen Glückwunsch, du findest deine Partnerin fürs Leben. Euer besonderer Tag war ein großer Aufwand, aber mit deiner neu gegründeten Familie erlebst du großes Glück und erfüllte Zeiten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-35000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_NO_CHILD,
                ],
                gewichtung: 10,
            ),
            "e210" => new EreignisCardDefinition(
                id: new CardId('e210'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du schließt den Geschirrspüler selbst an, ein Schlauch platzt, und Wasser läuft in die Wohnung darunter. Bei abgeschlossener Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-25000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e211" => new EreignisCardDefinition(
                id: new CardId('e211'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Ferienlager in Übersee',
                description: 'Du meldest dein Kind für ein Ferienlager im Ausland an. Die Reisekosten sind zwar hoch, aber du hast dadurch auch einmal Zeit für dich.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-24500),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e212" => new EreignisCardDefinition(
                id: new CardId('e212'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du lässt ein Handtuch über einer Stehlampe hängen – es fängt Feuer und beschädigt Teile des Hotelzimmers. Bei abgeschlossener Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-18000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e213" => new EreignisCardDefinition(
                id: new CardId('e213'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: '"Beim Volleyball beschädigst du versehentlich die Zahnprothese eines Mitspielers. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Ersatz übernommen."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                ),
                modifierIds: [
                    ModifierId::HAFTPFLICHTVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf3'),
                gewichtung: 4,
            ),
            "e214" => new EreignisCardDefinition(
                id: new CardId('e214'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deiner Nachbarin beeinträchtigen dich erheblich, weshalb es zu einem Rechtsstreit kommt. Die daraus resultierenden Gerichtskosten musst du tragen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e215" => new EreignisCardDefinition(
                id: new CardId('e215'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Deine Informationsflyer zu demokratischen Werten kommen so gut an, dass du eine weitere Auflage drucken lässt. Die Druckkosten trägst du erneut selbst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf153'),
                gewichtung: 4,
            ),
            "e216" => new EreignisCardDefinition(
                id: new CardId('e216'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Streichen der Wände fällst du von der Leiter und brichst dir die Schulter. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du eine einmalige Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5500),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e217" => new EreignisCardDefinition(
                id: new CardId('e217'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Reinigen der Dachrinne fällst du auf den Rücken und brichst mehrere Wirbel. Bei privater Unfallversicherung bekommst du eine einmalige Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e218" => new EreignisCardDefinition(
                id: new CardId('e218'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Weil du dich für sozial benachteiligte Menschen mit Beeinträchtigung engagierst und ihre gesellschaftliche Teilhabe förderst, wirst du mit einer bedeutenden Auszeichnung mit hoher Gewinnsumme geehrt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(60000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e219" => new EreignisCardDefinition(
                id: new CardId('e219'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du kümmerst dich gleichzeitig um deine Kinder und deine Eltern. Um allen gerecht zu werden, reduzierst du dieses Jahr deine Arbeitszeit. Dein Bruttogehalt wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e220" => new EreignisCardDefinition(
                id: new CardId('e220'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Du planst eine mehrmonatige Reise durch Südamerika und reduzierst dafür dieses Jahr deine Arbeitszeit. Dein Gehalt wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:80,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e221" => new EreignisCardDefinition(
                id: new CardId('e221'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kinderbetreuung',
                description: 'Dein Kind darf wegen aggressiven Verhaltens vorläufig nicht in den Kindergarten. Du betreust es selbst und reduzierst deine Arbeitszeit. Dein Gehalt passt sich an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:75,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e222" => new EreignisCardDefinition(
                id: new CardId('e222'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du kümmerst dich um ein Familienmitglied, das mehr Unterstützung braucht, und reduzierst für dieses Jahr deine Arbeitszeit. Dein Bruttogehalt passt sich entsprechend an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e223" => new EreignisCardDefinition(
                id: new CardId('e223'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Du nimmst dir bewusst Zeit für dein kreatives Projekt – egal ob Malen, Schreiben oder Musik. Deshalb reduzierst du deine Arbeitszeit. Dein Gehalt wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:70,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e224" => new EreignisCardDefinition(
                id: new CardId('e224'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Reduzierung Arbeitszeit',
                description: 'Ein Familienmitglied benötigt mehr Unterstützung im Alltag. Du übernimmst einen Teil der Pflege und reduzierst deshalb deine Arbeitszeit. Dein Gehalt wird dementsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:60,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e225" => new EreignisCardDefinition(
                id: new CardId('e225'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Lottogewinn',
                description: '"Glückwunsch! Du hast beim Roulette den Jackpot geknackt und eine saftige Gewinnsumme erhalten. Jetzt kannst du dir große Wünsche erfüllen oder ordentlich feiern."',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(60000),
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e226" => new EreignisCardDefinition(
                id: new CardId('e226'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Deine Tochter Elif ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage:10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e227" => new EreignisCardDefinition(
                id: new CardId('e227'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Deine Tochter Sophie ist geboren – herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Bruttogehalts (mindestens 1.000 €). Außerdem fallen einmalig Kosten für die Erstausstattung an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE,
                    ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyAdditionalLebenshaltungskostenPercentage:10,
                    modifyLebenshaltungskostenMinValue: new MoneyAmount(1000),
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e228" => new EreignisCardDefinition(
                id: new CardId('e228'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sucht',
                description: 'Du fotografierst bei Treffen ständig und teilst alles sofort auf Instagram. Das stört deine Freunde, da es dir wichtiger scheint als der Moment. Das Posten raubt dir zudem viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e229" => new EreignisCardDefinition(
                id: new CardId('e229'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Trennung',
                description: 'Deine Partnerin beendet die Beziehung mit dir. Du fällst tief in ein Loch. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e230" => new EreignisCardDefinition(
                id: new CardId('e230'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sabbatjahr',
                description: 'Für deine Weltreise nimmst du dir ein Sabbatjahr – auch wenn das bedeutet, dieses Jahr kein Gehalt zu bekommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +2,
                ),
                modifierIds: [
                    ModifierId::GEHALT_CHANGE,
                ],
                modifierParameters: new ModifierParameters(
                    modifyGehaltPercent:0,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e231" => new EreignisCardDefinition(
                id: new CardId('e231'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Flow',
                description: 'Dir geht gerade alles leicht von der Hand. ',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e232" => new EreignisCardDefinition(
                id: new CardId('e232'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geschenk',
                description: 'Dein Partner bereitet eine Überraschung vor und entführt dich auf eine traumhafte Kreuzfahrt durch die Karibik.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e233" => new EreignisCardDefinition(
                id: new CardId('e233'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Burn-Out',
                description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Um dich wieder zu erholen, gehst du in eine Rehaklinik. Setze eine Runde aus.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e234" => new EreignisCardDefinition(
                id: new CardId('e234'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Streit mit der Familie ',
                description: 'An Weihnachten gab es Streit mit deiner Schwester. Jetzt solltest du versuchen, die Beziehung wieder zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e235" => new EreignisCardDefinition(
                id: new CardId('e235'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du musst dich einer dringenden Operation unterziehen und liegst komplett flach. Du musst eine Runde aussetzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                ),
                modifierIds: [
                    ModifierId::AUSSETZEN,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e236" => new EreignisCardDefinition(
                id: new CardId('e236'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Chauffeurdienst',
                description: 'Herzlichen Glückwunsch! Du hast bei einer Verlosung einen einjährigen Chauffeurdienst gewonnen. Dadurch bist du schneller bei Terminen und kannst deine Freizeit mehr genießen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e237" => new EreignisCardDefinition(
                id: new CardId('e237'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Aufgrund deines Jobs hast du deine Freundschaften nicht gepflegt. Das musst du dringend ändern!',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e238" => new EreignisCardDefinition(
                id: new CardId('e238'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburtstagsparty',
                description: 'Du organisierst eine Geburtstagsparty für Freunde und Familie. Das kostet dich viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e239" => new EreignisCardDefinition(
                id: new CardId('e239'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kündigung',
                description: 'Du kündigst deinen Job, um auf Reisen neue Wege zu entdecken – dadurch entfällt aber auch dein Einkommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e240" => new EreignisCardDefinition(
                id: new CardId('e240'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbau Überstunden',
                description: 'Nach Jahren voller Überstunden gönnst du dir bewusst eine Auszeit und nutzt einen Monat für eine Reise durch Asien, um neue Energie zu tanken und deine Work-Life-Balance nachhaltig zu stärken.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e241" => new EreignisCardDefinition(
                id: new CardId('e241'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du wirst wegen unentschuldigtem Fehlen fristlos gekündigt und bekommst kein Gehalt mehr. Durch die Arbeitslosigkeit gewinnst du jedoch mehr freie Zeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e242" => new EreignisCardDefinition(
                id: new CardId('e242'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Kündigung',
                description: 'Du hast dich mit deinem Team zerstritten und dich mit deiner Chefin auf einen Aufhebungsvertrag geeinigt. Damit verlierst du deinen aktuellen Job und bist zunächst arbeitslos.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e243" => new EreignisCardDefinition(
                id: new CardId('e243'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Herzlichen Glückwunsch! Du gewinnst den Preis für Integration. Mit deinem Projekt „Bienen in Schulen“ hast du viele Bienenhotels gebaut und Kindern die Bedeutung der Bienen erklärt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e244" => new EreignisCardDefinition(
                id: new CardId('e244'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'soziales Engagement',
                description: 'Dein Chef schätzt dein soziales Engagement sehr und unterstützt deine Projekte gerne. Für das nächste Sommercamp erhältst du sofort Sonderurlaub.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e245" => new EreignisCardDefinition(
                id: new CardId('e245'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Da sich niemand für deinen Vorstandsposten im Tennisverein findet, übernimmst du das Amt für eine weitere Periode.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf151'),
                gewichtung: 4,
            ),
            "e246" => new EreignisCardDefinition(
                id: new CardId('e246'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Flow',
                description: 'Dir geht gerade alles leicht von der Hand. ',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e247" => new EreignisCardDefinition(
                id: new CardId('e247'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Flow',
                description: 'Dir geht gerade alles leicht von der Hand. ',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: 1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e248" => new EreignisCardDefinition(
                id: new CardId('e248'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Beziehungskrise',
                description: 'Du nimmst eine Paartherapie in Anspruch, um deine Beziehung zu retten – was bei den Obamas erfolgreich war, kann auch euch unterstützen. Allerdings bleibt dir dadurch vorerst weniger Zeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('e209'),
                gewichtung: 4,
            ),
            "e249" => new EreignisCardDefinition(
                id: new CardId('e249'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'kein Finanzbericht lesen',
                description: 'Du hast mehr Freizeit, weil du den Börsenbericht nicht mehr liest. Dafür kannst du eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "e250" => new EreignisCardDefinition(
                id: new CardId('e250'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'kein Finanzbericht lesen',
                description: 'Du hast mehr Freizeit, weil du den Börsenbericht nicht mehr liest. Dafür kannst du eine Runde nicht investieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                    ModifierId::INVESTITIONSSPERRE,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 1,
            ),
            "wb1" => new WeiterbildungCardDefinition(
                id: new CardId('wb1'),
                description: 'Welche handlungspolitische Maßnahme dient dem Schutz der heimischen Wirtschauft vor ausländischer Konkurrenz? ',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Einführung von Zöllen auf ausländische Waren", true),
                    new AnswerOption(new AnswerId("d"), "Abschaffung von Zöllen"),
                    new AnswerOption(new AnswerId("b"), "Freier Zugang für alle ausländischen Anbieter"),
                ],
            ),
            "wb2" => new WeiterbildungCardDefinition(
                id: new CardId('wb2'),
                description: 'Ein Unternehmen verkauft 120 Fahrräder für jeweils 230 € das Stück. Für Löhne, Miete, Einkauf etc. fallen Gesamtkosten in Höhe von 26.000 € an. Der Umsatz beträgt 1600€. ',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Falsch", true),
                    new AnswerOption(new AnswerId("a"), "Wahr"),
                ],
            ),
            "wb3" => new WeiterbildungCardDefinition(
                id: new CardId('wb3'),
                description: 'Welche der folgenden Optionen ist KEIN typisches Motiv für die Gründung eines Unternehmens? ',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Langfristige Arbeitsplatzgarantie", true),
                    new AnswerOption(new AnswerId("d"), "Unabhängigkeit"),
                    new AnswerOption(new AnswerId("c"), "Selbstverwirklichung"),
                    new AnswerOption(new AnswerId("a"), "Nutzung von Marktchancen"),
                ],
            ),
            "wb4" => new WeiterbildungCardDefinition(
                id: new CardId('wb4'),
                description: 'Welches der folgenden Interessen ist KEIN typisches Interesse von Arbeitnehmerinnen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Maximale Kosteneffizienz und unternehmerische Flexibilität", true),
                    new AnswerOption(new AnswerId("c"), "Angemessene Vergütung und soziale Absicherung"),
                    new AnswerOption(new AnswerId("a"), "Tarifliche Entlohnung und Arbeitsplatzsicherheit"),
                ],
            ),
            "wb5" => new WeiterbildungCardDefinition(
                id: new CardId('wb5'),
                description: 'Welche der folgenden Prinzipien beschreiben das ökonomische Prinzip?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Minimalprinzip und Maximalprinzip", true),
                    new AnswerOption(new AnswerId("c"), "Rentabilitätsprinzip und Effizienzprinzip"),
                    new AnswerOption(new AnswerId("b"), "Opportunitätsprinzip und Investitionsprinzip"),
                    new AnswerOption(new AnswerId("a"), "Gewinnprinzip und Sparprinzip"),
                ],
            ),
            "wb6" => new WeiterbildungCardDefinition(
                id: new CardId('wb6'),
                description: 'Ein Unternehmen verkauft 120 Fahrräder zu je 230 €. Die Gesamtkosten betragen 26.000 €. Der Gewinn beträgt 1.600 €.',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Wahr", true),
                    new AnswerOption(new AnswerId("a"), "Falsch"),
                ],
            ),
            "wb7" => new WeiterbildungCardDefinition(
                id: new CardId('wb7'),
                description: 'Welche der folgenden Optionen sind mögliche Ursachen für Überschuldung?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Übermäßige Verschuldung und negative Geschäftsentwicklung", true),
                    new AnswerOption(new AnswerId("d"), "Hohe laufende Einnahmen und starke Liquidität"),
                    new AnswerOption(new AnswerId("a"), "Hohe Rücklagenbildung und starke Kapitalreserven"),
                ],
            ),
            "wb8" => new WeiterbildungCardDefinition(
                id: new CardId('wb8'),
                description: 'Welche Wirtschaftsordnung strebt die Bundesrepublik Deutschland an?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Soziale Marktwirtschaft", true),
                    new AnswerOption(new AnswerId("d"), "Zentralverwaltungswirtschaft"),
                    new AnswerOption(new AnswerId("a"), "Marktwirtschaft"),
                    new AnswerOption(new AnswerId("c"), "Zentrale Planwirtschaft"),
                ],
            ),
            "wb9" => new WeiterbildungCardDefinition(
                id: new CardId('wb9'),
                description: 'Wer einen Kredit in kleineren Raten über eine längere Zeit zurückzahlt, zahlt insgesamt mehr Zinsen als bei schnellerer Rückzahlung.',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "wahr", true),
                    new AnswerOption(new AnswerId("d"), "falsch"),
                ],
            ),
            "wb10" => new WeiterbildungCardDefinition(
                id: new CardId('wb10'),
                description: 'Wie hoch sind die Jahreszinsen für ein Darlehen über 2.000 €, wenn ein Zinssatz von 5 % vereinbart wurde? ',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "100 €", true),
                    new AnswerOption(new AnswerId("a"), "150 €"),
                    new AnswerOption(new AnswerId("b"), "200 €"),
                ],
            ),
            "wb11" => new WeiterbildungCardDefinition(
                id: new CardId('wb11'),
                description: 'Welche Aussage ist richtig?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Die Krankenversicherungsbeiträge sind je nach Krankenkasse unterschiedlich hoch. ", true),
                    new AnswerOption(new AnswerId("c"), "In der Regel werden die Sozialversicherungsbeiträge fast ausschließlich von Arbeitnehmerinnen aufgebracht. "),
                    new AnswerOption(new AnswerId("b"), "Der Nettolohn ist häufig höher als der Bruttolohn."),
                ],
            ),
            "wb12" => new WeiterbildungCardDefinition(
                id: new CardId('wb12'),
                description: 'Welche Interessen teilen Arbeitgeberinnen und Arbeitnehmerinnen häufig?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Arbeitsplatzsicherheit und langfristiger Erfolg", true),
                    new AnswerOption(new AnswerId("a"), "Mehr Arbeitszeit und weniger Lohn"),
                    new AnswerOption(new AnswerId("c"), "Weniger Belastung und niedrigere Kosten"),
                    new AnswerOption(new AnswerId("d"), "Bessere Bedingungen und hohe Produktivität"),
                ],
            ),
            "wb13" => new WeiterbildungCardDefinition(
                id: new CardId('wb13'),
                description: 'Angebot und Nachfrage - Welche Aussage ist richtig?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Mit steigendem Preis eines Gutes, sinkt laut Nachfragegesetz die Nachfrage eines Gutes.", true),
                    new AnswerOption(new AnswerId("b"), "Bei Nachfrageüberhang wird von einem Gut weniger nachgefragt, als verfügbar.  "),
                ],
            ),
            "wb14" => new WeiterbildungCardDefinition(
                id: new CardId('wb14'),
                description: 'Welche Faktoren beeinflussen die Nachfrage?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Einkommen der Konsumenten und Preisvorstellungen", true),
                    new AnswerOption(new AnswerId("c"), "Marktgröße und Wettbewerbsverhältnisse"),
                    new AnswerOption(new AnswerId("d"), "Technologie und Innovationskraft"),
                    new AnswerOption(new AnswerId("b"), "Produktionskosten und Ressourcenverfügbarkeit"),
                ],
            ),
            "wb15" => new WeiterbildungCardDefinition(
                id: new CardId('wb15'),
                description: 'Wie heißt der Preis, bei dem Angebot und Nachfrage genau übereinstimmen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Gleichgewichtspreis", true),
                    new AnswerOption(new AnswerId("a"), "Höchstpreis"),
                    new AnswerOption(new AnswerId("b"), "Mindestpreis"),
                ],
            ),
            "wb16" => new WeiterbildungCardDefinition(
                id: new CardId('wb16'),
                description: 'Welche Aussage trifft NICHT zu?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Variable Kosten sind unabhängig von der Produktionsmenge, wenn der Fixkostenanteil hoch ist.", true),
                    new AnswerOption(new AnswerId("d"), "Variable Kosten können mit der Produktionsmenge steigen."),
                    new AnswerOption(new AnswerId("b"), "Variable Kosten sind abhängig von der produzierten Stückzahl."),
                    new AnswerOption(new AnswerId("c"), "Variable Kosten verändern sich bei steigender Produktionsmenge."),
                ],
            ),
            "wb17" => new WeiterbildungCardDefinition(
                id: new CardId('wb17'),
                description: 'Welche Formel entspricht dem wirtschaftlichen Begriff “Umsatz”?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Umsatz = Menge × Preis ", true),
                    new AnswerOption(new AnswerId("c"), "Umsatz = Gewinn – Steuern"),
                    new AnswerOption(new AnswerId("d"), "Umsatz = Kosten × Preis"),
                    new AnswerOption(new AnswerId("a"), "Umsatz = Menge + Kosten"),
                ],
            ),
            "wb18" => new WeiterbildungCardDefinition(
                id: new CardId('wb18'),
                description: 'Ein Annuitätenkredit wird mit konstanten Zahlungen aus Zins und Tilgung zurückgezahlt. Nach der Hälfte der Laufzeit ist die Restschuld meist geringer als die Hälfte des ursprünglichen Kredits.',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "wahr", true),
                    new AnswerOption(new AnswerId("c"), "Falsch"),
                ],
            ),
            "wb19" => new WeiterbildungCardDefinition(
                id: new CardId('wb19'),
                description: 'Welche der folgenden Indikatorenmisst den Wohlstand alternativ zum BIP (Bruttoinlandsprodukt)?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Pro-Kopf Einkommen", true),
                    new AnswerOption(new AnswerId("a"), "Inflationsrate"),
                    new AnswerOption(new AnswerId("b"), "Unternehmensgewinne"),
                    new AnswerOption(new AnswerId("c"), "Arbeitslosenquote"),
                ],
            ),
            "wb20" => new WeiterbildungCardDefinition(
                id: new CardId('wb20'),
                description: 'Welcher Unterschied beschreibt den Unterschied zwischen nominalem und realem BIP?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Nominales BIP nutzt aktuelle Preise, reales berücksichtigt Preisänderungen.", true),
                    new AnswerOption(new AnswerId("d"), "Nominales BIP nutzt konstante Preise, reales aktuelle."),
                ],
            ),
            "wb21" => new WeiterbildungCardDefinition(
                id: new CardId('wb21'),
                description: 'Welche Interessen vertreten Arbeitgeberinnen typischerweise?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Kostensenkung und flexible Personalplanung", true),
                    new AnswerOption(new AnswerId("c"), "Hohe Löhne und maximale Sicherheit"),
                    new AnswerOption(new AnswerId("d"), "Mehr Urlaub und kürzere Arbeitszeit"),
                    new AnswerOption(new AnswerId("a"), "Mitbestimmung und Arbeitsplatzgarantie"),
                ],
            ),
            "wb22" => new WeiterbildungCardDefinition(
                id: new CardId('wb22'),
                description: 'Was versteht man unter dem Gewinn in der Betriebswirtschaft?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Die Differenz zwischen Umsatz und Kosten ", true),
                    new AnswerOption(new AnswerId("b"), "Die Summe aus Umsatz und Kosten"),
                    new AnswerOption(new AnswerId("d"), "Der Umsatz ohne Abzüge"),
                    new AnswerOption(new AnswerId("a"), "Die Menge verkaufter Produkte"),
                ],
            ),
            "wb23" => new WeiterbildungCardDefinition(
                id: new CardId('wb23'),
                description: 'Was kann ein Nachteil der sozialen Marktwirtschaft sein?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Hohe Staatsausgaben und mögliche Bürokratie", true),
                    new AnswerOption(new AnswerId("b"), "Kein Sozialsystem, völlig freier Markt"),
                    new AnswerOption(new AnswerId("c"), "Kein sozialer Ausgleich, kein Staatseingriff"),
                ],
            ),
            "wb24" => new WeiterbildungCardDefinition(
                id: new CardId('wb24'),
                description: 'Welche Faktoren bestimmen die Höhe von Löhnen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Qualifikation der Arbeitnehmenden und Branchenentwicklung", true),
                    new AnswerOption(new AnswerId("d"), "Individuelle Sparziele und betriebliche Sozialangebote"),
                    new AnswerOption(new AnswerId("b"), "Subjektive Zufriedenheit der Arbeitgebenden und Anzahl der Urlaubstage"),
                ],
            ),
            "wb25" => new WeiterbildungCardDefinition(
                id: new CardId('wb25'),
                description: 'Welche Aussage trifft auf ein Sparbuch zu?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Ein- und Auszahlung sind grundsätzlich kostenfrei. ", true),
                    new AnswerOption(new AnswerId("d"), "Hoher Zins, aber Kursrisiko. "),
                    new AnswerOption(new AnswerId("b"), "Niedriger Zins, aber für den zahlungsverkehr nutzbar. "),
                ],
            ),
            "wb26" => new WeiterbildungCardDefinition(
                id: new CardId('wb26'),
                description: 'Was passiert, wenn der Leitzins der Europäischen Zentralbank steigt?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Kredite werden teurer, Sparzinsen steigen", true),
                    new AnswerOption(new AnswerId("c"), "Kredite werden günstiger, Sparzinsen sinken"),
                    new AnswerOption(new AnswerId("b"), "Der Euro verliert automatisch an Wert"),
                ],
            ),
            "wb27" => new WeiterbildungCardDefinition(
                id: new CardId('wb27'),
                description: '"Welche Anlageform gilt als besonders sicher, aber mit niedrigerer Rendite?"',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Tagesgeldkonto", true),
                    new AnswerOption(new AnswerId("a"), "Kryptowährungen"),
                    new AnswerOption(new AnswerId("b"), "Aktienfonds"),
                ],
            ),
            "wb28" => new WeiterbildungCardDefinition(
                id: new CardId('wb28'),
                description: 'Welche Strategie dient zur Risikominimierung bei Investitonen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Diversifikation des Portfolios", true),
                    new AnswerOption(new AnswerId("a"), "Kredite aufnehmen, um mehr investieren zu können"),
                    new AnswerOption(new AnswerId("d"), "Alle Investitionen in eine einzige Aktie stecken."),
                ],
            ),
            "wb29" => new WeiterbildungCardDefinition(
                id: new CardId('wb29'),
                description: '"Was bedeutet ""progressive Besteuerung"" ?"',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Der Steuersatz steigt mit zunehmendem Einkommen. ", true),
                    new AnswerOption(new AnswerId("d"), "Alle zahlen denselben Prozentsatz ihres Einkommens."),
                    new AnswerOption(new AnswerId("c"), "Der Staat erhebt nur Steuern auf hohe Erbschaften."),
                ],
            ),
            "wb30" => new WeiterbildungCardDefinition(
                id: new CardId('wb30'),
                description: 'Was beeinflusst typischerweise eine Investitionsentscheidung einer Privatperson?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Risiko der Investition und erwartete Rendite", true),
                    new AnswerOption(new AnswerId("d"), "Steuern und Arbeitsstunden"),
                    new AnswerOption(new AnswerId("b"), "Politische Stabilität und Unternehmensgröße"),
                    new AnswerOption(new AnswerId("c"), "Gehalt und Urlaubsanspruch"),
                ],
            ),
            "wb31" => new WeiterbildungCardDefinition(
                id: new CardId('wb31'),
                description: 'Wie unterscheidet sich eine Aktie von einer Anleihe?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Eine Aktie ist ein Unternehmensantiel, während eine Anleihe ein Darlehen an ein Unternehmen ist.", true),
                    new AnswerOption(new AnswerId("d"), "Aktie garantiert feste Zinsen."),
                    new AnswerOption(new AnswerId("c"), "Aktie nur zu Unternehmensgründung."),
                    new AnswerOption(new AnswerId("a"), "Aktie bringt immer höhere Rendite."),
                ],
            ),
            "wb32" => new WeiterbildungCardDefinition(
                id: new CardId('wb32'),
                description: 'Welche der folgenden Optionen beschreibt ein Risiko bei Geldanlagen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), "Inflation und mögliche Wertverluste", true),
                    new AnswerOption(new AnswerId("d"), "Langfristige Sicherheit"),
                    new AnswerOption(new AnswerId("a"), "Steuerersparnisse"),
                    new AnswerOption(new AnswerId("b"), "Hohe Renditen ohne Unsicherheit"),
                ],
            ),
            "wb33" => new WeiterbildungCardDefinition(
                id: new CardId('wb33'),
                description: 'Was unterscheidet kurzfristige von langfristigen Investitionen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Kurzfr. Investitionen haben geringere Laufzeit und bieten schnelle Rückflüsse.", true),
                    new AnswerOption(new AnswerId("a"), "Kurzfr. Investitionen werden nur in Aktien getätigt, langfr. nur in Immobilien."),
                    new AnswerOption(new AnswerId("c"), "Kurzfr. Investitionen sind für Unternehmen und langfr. nur für Privatpersonen sinnvoll."),
                ],
            ),
            "wb34" => new WeiterbildungCardDefinition(
                id: new CardId('wb34'),
                description: 'Was ist der Zinseszinseffekt?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Zinsen werden auf bereits erhaltene Zinsen berechnet.", true),
                    new AnswerOption(new AnswerId("b"), "Zinsen steigen immer jährlich."),
                    new AnswerOption(new AnswerId("d"), "Zinsen werden nur auf das ursprüngliche Kapital berechnet."),
                ],
            ),
            "wb35" => new WeiterbildungCardDefinition(
                id: new CardId('wb35'),
                description: '"Was bedeutet ""Liquidität"" in Bezug auf Investitionen?"',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Die Geschwindigkeit, mit der eine Investition verkauft werden kann.", true),
                    new AnswerOption(new AnswerId("a"), "Der Gewinn aus einer Investition."),
                    new AnswerOption(new AnswerId("b"), "Der langfristige Wert einer Investition."),
                ],
            ),
            "wb36" => new WeiterbildungCardDefinition(
                id: new CardId('wb36'),
                description: 'Welche Aussage beschreibt am besten eine Anleihe?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Festverzinsliches Wertpapier, mit dem ein Anleger einem Unternehmen oder Staat Geld leiht", true),
                    new AnswerOption(new AnswerId("c"), "Unternehmensanteil mit Anspruch auf Gewinnbeteiligung"),
                    new AnswerOption(new AnswerId("b"), "Eine kurzfristige Investition in Aktien"),
                ],
            ),
            "wb37" => new WeiterbildungCardDefinition(
                id: new CardId('wb37'),
                description: 'Wie unterscheiden sich ETFs von traditionellen Investmentfonds?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "ETFs sind meist passiv und bilden einen Index ab", true),
                    new AnswerOption(new AnswerId("b"), "ETFs werden nicht börslich gehandelt"),
                    new AnswerOption(new AnswerId("d"), "ETFs haben höhere Verwaltungskosten"),
                ],
            ),
            "wb38" => new WeiterbildungCardDefinition(
                id: new CardId('wb38'),
                description: 'Welche der folgenden Aussagen beschreibt am besten einen ETF?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Ein passiv verwalteter Fonds, der einen Index nachbildet.", true),
                    new AnswerOption(new AnswerId("a"), "Ein aktiver Fonds, der nur in einzelen Aktien investiert."),
                    new AnswerOption(new AnswerId("b"), "Ein Investment in Immobilien."),
                ],
            ),
            "wb39" => new WeiterbildungCardDefinition(
                id: new CardId('wb39'),
                description: 'Was ist ein wesentlicher Vorteil von Immobilieninvestitionen im Vergleich zu Aktien?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), "Potenzielle steuerliche Vorteile", true),
                    new AnswerOption(new AnswerId("d"), "Geringeres Risiko"),
                    new AnswerOption(new AnswerId("a"), "Höhere Liquidität"),
                ],
            ),
            "wb40" => new WeiterbildungCardDefinition(
                id: new CardId('wb40'),
                description: '"Was passiert in einem Markt mit hoher Nachfrage und geringem Angebot?"',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Der Preis steigt", true),
                    new AnswerOption(new AnswerId("c"), "Der Preis bleibt konstant"),
                    new AnswerOption(new AnswerId("a"), "Der Preis sinkt"),
                ],
            ),
            "wb41" => new WeiterbildungCardDefinition(
                id: new CardId('wb41'),
                description: 'Was passiert typischerweise, wenn der Preis eines Guts steigt und das Angebot zunimmt?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Die Nachfrage sinkt", true),
                    new AnswerOption(new AnswerId("c"), "Die Nachfrage bleibt unverändert"),
                    new AnswerOption(new AnswerId("b"), "Die Nachfrage steigt"),
                ],
            ),
            "wb42" => new WeiterbildungCardDefinition(
                id: new CardId('wb42'),
                description: 'Welche Faktoren bestimmen das Angebot?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), "Produktionskosten und Ressorcenverfügbarkeit ", true),
                    new AnswerOption(new AnswerId("c"), "Verbraucherpräferenzen und saisonale Trends"),
                    new AnswerOption(new AnswerId("b"), "Preispolitik und staatliche Eingriffe"),
                    new AnswerOption(new AnswerId("d"), "Einkommen der Konsumentinnen"),
                ],
            ),
            "wb43" => new WeiterbildungCardDefinition(
                id: new CardId('wb43'),
                description: 'Welche Aussage beschreibt eine Einschränkung des einfachen Wirtschaftskreislaufs am treffendsten?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Er ignoriert institutionelle Sektoren wie Staat, Finanzsystem und Ausland.", true),
                    new AnswerOption(new AnswerId("a"), "Er bildet nur den realen Güterstrom, nicht aber monetäre Transaktionen ab."),
                    new AnswerOption(new AnswerId("b"), "Er berücksichtigt lediglich die Rolle von Konsumenten, nicht aber von Produzenten."),
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
