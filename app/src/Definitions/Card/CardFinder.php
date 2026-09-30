<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\Dto\AnswerOption;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\CardWithYear;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
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

    /**
     * Cards that were removed from the game. They are never drawn, but can still be found by id,
     * see {@see self::getLegacyCards()}
     *
     * @var CardDefinition[] $legacyCards
     */
    private array $legacyCards;

    private static ?self $instance = null;

    /**
     * @param CardDefinition[] $cards
     * @param CardDefinition[] $legacyCards
     */
    private function __construct(array $cards, array $legacyCards = [])
    {
        $this->cards = $cards;
        $this->legacyCards = $legacyCards;
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

    /**
     * Returns all cards including the legacy cards, e.g. to validate all definitions.
     * Calling `...ForTesting` functions outside of test code will be caught by phpstan.
     *
     * @return CardDefinition[]
     */
    public function getAllCardsIncludingLegacyForTesting(): array
    {
        return $this->cards + $this->legacyCards;
    }

    /**
     * Calling `...ForTesting` functions outside of test code will be caught by phpstan.
     *
     * @param CardDefinition[] $legacyCards
     * @return void
     */
    public function overrideLegacyCardsForTesting(array $legacyCards): void
    {
        self::getInstance()->legacyCards = $legacyCards;
    }

    private static function initialize(): self
    {
        self::$instance = new self([
            "inv1" => new ImmobilienCardDefinition(
                id: new CardId('inv1'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Studierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-75000),
                ),
                annualRent: new MoneyAmount(3000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv2" => new ImmobilienCardDefinition(
                id: new CardId('inv2'),
                title: 'Kauf Wohnung',
                description: 'Eine kleine Wohnung in einem Studierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                ),
                annualRent: new MoneyAmount(2000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv3" => new ImmobilienCardDefinition(
                id: new CardId('inv3'),
                title: 'Kauf Wohnung',
                description: 'Ein renoviertes Loft mit DINK-Mieterinnen (Double Income, No Kids) steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(8000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv4" => new ImmobilienCardDefinition(
                id: new CardId('inv4'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100000),
                ),
                annualRent: new MoneyAmount(3600),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv5" => new ImmobilienCardDefinition(
                id: new CardId('inv5'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einer Seniorenwohnanlage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150000),
                ),
                annualRent: new MoneyAmount(6000),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv6" => new ImmobilienCardDefinition(
                id: new CardId('inv6'),
                title: 'Kauf Wohnung',
                description: 'Eine frisch renovierte Wohnung mit solventen Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100000),
                ),
                annualRent: new MoneyAmount(3600),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv7" => new ImmobilienCardDefinition(
                id: new CardId('inv7'),
                title: 'Kauf Haus',
                description: 'Ein Haus in einem Brennpunktviertel steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-175000),
                ),
                annualRent: new MoneyAmount(7700),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv8" => new ImmobilienCardDefinition(
                id: new CardId('inv8'),
                title: 'Kauf Haus',
                description: 'Ein sanierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(8800),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv9" => new ImmobilienCardDefinition(
                id: new CardId('inv9'),
                title: 'Kauf Haus',
                description: 'Ein renovierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(8800),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv10" => new ImmobilienCardDefinition(
                id: new CardId('inv10'),
                title: 'Kauf Haus',
                description: 'Ein Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(12000),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv11" => new ImmobilienCardDefinition(
                id: new CardId('inv11'),
                title: 'Kauf Haus',
                description: 'Ein Haus in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-350000),
                ),
                annualRent: new MoneyAmount(12600),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv12" => new ImmobilienCardDefinition(
                id: new CardId('inv12'),
                title: 'Kauf Haus',
                description: 'Ein neu renoviertes Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_1,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-400000),
                ),
                annualRent: new MoneyAmount(14400),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv13" => new ImmobilienCardDefinition(
                id: new CardId('inv13'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Studierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150000),
                ),
                annualRent: new MoneyAmount(6380),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv14" => new ImmobilienCardDefinition(
                id: new CardId('inv14'),
                title: 'Kauf Wohnung',
                description: 'Eine frisch renovierte Wohnung mit solventen Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-270000),
                ),
                annualRent: new MoneyAmount(10400),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv15" => new ImmobilienCardDefinition(
                id: new CardId('inv15'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einer Seniorenwohnanlage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-280000),
                ),
                annualRent: new MoneyAmount(11900),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv16" => new ImmobilienCardDefinition(
                id: new CardId('inv16'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-290000),
                ),
                annualRent: new MoneyAmount(11160),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv17" => new ImmobilienCardDefinition(
                id: new CardId('inv17'),
                title: 'Kauf Wohnung',
                description: 'Ein renoviertes Loft mit DINK-Mieterinnen (Double Income, No Kids) steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(12750),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv18" => new ImmobilienCardDefinition(
                id: new CardId('inv18'),
                title: 'Kauf Haus',
                description: 'Ein sanierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-210000),
                ),
                annualRent: new MoneyAmount(9770),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv19" => new ImmobilienCardDefinition(
                id: new CardId('inv19'),
                title: 'Kauf Haus',
                description: 'Ein renovierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-210000),
                ),
                annualRent: new MoneyAmount(9770),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv20" => new ImmobilienCardDefinition(
                id: new CardId('inv20'),
                title: 'Kauf Haus',
                description: 'Ein Haus in einem Brennpunktviertel steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200000),
                ),
                annualRent: new MoneyAmount(9300),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv21" => new ImmobilienCardDefinition(
                id: new CardId('inv21'),
                title: 'Kauf Haus',
                description: 'Ein neu renoviertes Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-420000),
                ),
                annualRent: new MoneyAmount(16170),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv22" => new ImmobilienCardDefinition(
                id: new CardId('inv22'),
                title: 'Kauf Haus',
                description: 'Ein Haus in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-370000),
                ),
                annualRent: new MoneyAmount(14240),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv23" => new ImmobilienCardDefinition(
                id: new CardId('inv23'),
                title: 'Kauf Haus',
                description: 'Ein Haus im Grünen soll verkauft werden.',
                phaseId: LebenszielPhaseId::PHASE_2,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(12750),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv24" => new ImmobilienCardDefinition(
                id: new CardId('inv24'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einem neuen Studierendenwohnheim steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-155000),
                ),
                annualRent: new MoneyAmount(6980),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv25" => new ImmobilienCardDefinition(
                id: new CardId('inv25'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in einer Seniorenwohnanlage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-290000),
                ),
                annualRent: new MoneyAmount(13050),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv26" => new ImmobilienCardDefinition(
                id: new CardId('inv26'),
                title: 'Kauf Wohnung',
                description: 'Eine frisch renovierte Wohnung mit solventen Mieterinnen steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-290000),
                ),
                annualRent: new MoneyAmount(11890),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv27" => new ImmobilienCardDefinition(
                id: new CardId('inv27'),
                title: 'Kauf Wohnung',
                description: 'Eine Wohnung in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-310000),
                ),
                annualRent: new MoneyAmount(12710),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv28" => new ImmobilienCardDefinition(
                id: new CardId('inv28'),
                title: 'Kauf Wohnung',
                description: 'Ein renoviertes Loft mit DINK-Mieterinnen (Double Income, No Kids) steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-350000),
                ),
                annualRent: new MoneyAmount(15750),
                immobilienTyp: ImmobilienType::WOHNUNG,
            ),
            "inv29" => new ImmobilienCardDefinition(
                id: new CardId('inv29'),
                title: 'Kauf Haus',
                description: 'Ein sanierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-220000),
                ),
                annualRent: new MoneyAmount(10780),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv30" => new ImmobilienCardDefinition(
                id: new CardId('inv30'),
                title: 'Kauf Haus',
                description: 'Ein neu renoviertes Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-440000),
                ),
                annualRent: new MoneyAmount(18040),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv31" => new ImmobilienCardDefinition(
                id: new CardId('inv31'),
                title: 'Kauf Haus',
                description: 'Ein renovierungsbedürftiges Haus steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-280000),
                ),
                annualRent: new MoneyAmount(13720),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv32" => new ImmobilienCardDefinition(
                id: new CardId('inv32'),
                title: 'Kauf Haus',
                description: 'Ein Haus in einem Brennpunktviertel steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300000),
                ),
                annualRent: new MoneyAmount(14700),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv33" => new ImmobilienCardDefinition(
                id: new CardId('inv33'),
                title: 'Kauf Haus',
                description: 'Ein Haus wird zum Verkauf angeboten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-330000),
                ),
                annualRent: new MoneyAmount(14850),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "inv34" => new ImmobilienCardDefinition(
                id: new CardId('inv34'),
                title: 'Kauf Haus',
                description: 'Ein Haus in hervorragender Lage steht zum Verkauf.',
                phaseId: LebenszielPhaseId::PHASE_3,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-380000),
                ),
                annualRent: new MoneyAmount(15580),
                immobilienTyp: ImmobilienType::HAUS,
            ),
            "buk1" => new KategorieCardDefinition(
                id: new CardId('buk1'),
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
            "buk2" => new KategorieCardDefinition(
                id: new CardId('buk2'),
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
            "buk3" => new KategorieCardDefinition(
                id: new CardId('buk3'),
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
            "buk4" => new KategorieCardDefinition(
                id: new CardId('buk4'),
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
            "buk5" => new KategorieCardDefinition(
                id: new CardId('buk5'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ausbildung zur Skilehrkraft',
                description: 'Erfülle dir deinen Traum und absolviere eine Ausbildung zur Skilehrkraft. Neben technischem Wissen eignest du dir dabei auch geografische und pädagogische Kenntnisse an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk6" => new KategorieCardDefinition(
                id: new CardId('buk6'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Übungsleiterschein im Kinderturnen',
                description: 'Erfülle dir deinen Traum und mache deinen Übungsleiterschein im Kinderturnen. Neben technischem und pädagogischem Wissen eignest du dir dabei auch Kenntnisse über gruppendynamische Prozesse an.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk7" => new KategorieCardDefinition(
                id: new CardId('buk7'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Bergtourleitung',
                description: 'Verwirkliche deinen Traum und absolviere die Ausbildung zur Bergtourleitung. Dabei erwirbst du nicht nur technisches, sondern auch geografisches und pädagogisches Wissen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk8" => new KategorieCardDefinition(
                id: new CardId('buk8'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Nachhilfe',
                description: 'Du willst deine Noten verbessern und gehst deshalb zur Nachhilfe.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-600),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk9" => new KategorieCardDefinition(
                id: new CardId('buk9'),
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
            "buk10" => new KategorieCardDefinition(
                id: new CardId('buk10'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Reise',
                description: 'Reise in ein fremdes Land, um deinen kulturellen Horizont zu erweitern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk11" => new KategorieCardDefinition(
                id: new CardId('buk11'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kulturabo',
                description: 'Schließe ein Kulturabo deiner Stadt ab und besuche regelmäßig interessante Premieren.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk12" => new KategorieCardDefinition(
                id: new CardId('buk12'),
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
            "buk13" => new KategorieCardDefinition(
                id: new CardId('buk13'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Start-up-Wettbewerb',
                description: 'Du beteiligst dich an einem Start-up-Wettbewerb und investierst 2.000 € in die Entwicklung eines Prototyps.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk14" => new KategorieCardDefinition(
                id: new CardId('buk14'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Seminarwochenende',
                description: 'Du nimmst an einem Seminarwochenende der Börse Frankfurt teil, um verschiedene Investmentstrategien kennenzulernen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-250),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk15" => new KategorieCardDefinition(
                id: new CardId('buk15'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ausbildung Segellehrkraft',
                description: 'Verwirkliche deinen Traum und absolviere eine Ausbildung zur Segellehrkraft. Dabei eignest du dir nicht nur technisches Wissen an, sondern lernst auch viel über gruppendynamische Prozesse.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk16" => new KategorieCardDefinition(
                id: new CardId('buk16'),
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
            "buk17" => new KategorieCardDefinition(
                id: new CardId('buk17'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachreise nach England',
                description: 'Reise nach England, um deine Sprachkenntnisse zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-700),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk18" => new KategorieCardDefinition(
                id: new CardId('buk18'),
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
            "buk19" => new KategorieCardDefinition(
                id: new CardId('buk19'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Online-Seminar',
                description: 'Um deine Computerkenntnisse zu verbessern, besuchst du ein Online-Seminar.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-100),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk20" => new KategorieCardDefinition(
                id: new CardId('buk20'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Jagdverein',
                description: 'Werde Mitglied im Jagdverein und triff dich mit anderen Nachwuchstalenten in der freien Natur.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk21" => new KategorieCardDefinition(
                id: new CardId('buk21'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Berufliches Profil schärfen',
                description: 'Du nutzt ein Wochenende, um deine Profile in beruflichen Netzwerken für Arbeitgebende attraktiv zu gestalten. Dafür verlierst du einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk22" => new KategorieCardDefinition(
                id: new CardId('buk22'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mehr Erfolg durch Fitness',
                description: 'Regelmäßiger Sport hält dich fit und kann dir auch im Beruf helfen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk23" => new KategorieCardDefinition(
                id: new CardId('buk23'),
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
            "buk24" => new KategorieCardDefinition(
                id: new CardId('buk24'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Karriere-Booster: Chinesisch',
                description: 'Stell dich der Herausforderung einer der anspruchsvollsten Sprachen der Welt: Bei deinem Aufenthalt in China kannst du dein Chinesisch deutlich verbessern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk25" => new KategorieCardDefinition(
                id: new CardId('buk25'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ortswechsel',
                description: 'Zieh für einen Job mit guten Aufstiegschancen in eine neue Stadt – auch wenn der Umzug Geld kostet.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk26" => new KategorieCardDefinition(
                id: new CardId('buk26'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ortswechsel',
                description: 'Für eine Beförderung innerhalb deines Unternehmens ziehst du in ein anderes Bundesland und hast so die Chance, dein Privatleben neu zu gestalten.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk27" => new KategorieCardDefinition(
                id: new CardId('buk27'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Auslandsaufenthalt',
                description: 'Für deine Karriere ziehst du für sechs Monate nach Singapur.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk28" => new KategorieCardDefinition(
                id: new CardId('buk28'),
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
            "buk29" => new KategorieCardDefinition(
                id: new CardId('buk29'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Feierabendgetränk',
                description: 'Mit deiner Kollegschaft ziehst du regelmäßig nach dem Feierabend um die Häuser und verbesserst damit dein berufliches Netzwerk.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk30" => new KategorieCardDefinition(
                id: new CardId('buk30'),
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
            "buk31" => new KategorieCardDefinition(
                id: new CardId('buk31'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder absolvierten Weiterbildung wächst nicht nur dein Wissen – auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-600),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk32" => new KategorieCardDefinition(
                id: new CardId('buk32'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Networking',
                description: 'Knüpfe wertvolle berufliche Kontakte und erweitere deinen Horizont durch die Teilnahme an internationalen Konferenzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk33" => new KategorieCardDefinition(
                id: new CardId('buk33'),
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
            "buk34" => new KategorieCardDefinition(
                id: new CardId('buk34'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Persönlichkeitscoaching',
                description: 'Um dein Auftreten zu stärken, entscheidest du dich für ein Persönlichkeitscoaching.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-600),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk35" => new KategorieCardDefinition(
                id: new CardId('buk35'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachlern-App',
                description: 'Mithilfe einer Sprachlern-App versuchst du, dir erste Grundkenntnisse in einer dir unbekannten Sprache anzueignen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk36" => new KategorieCardDefinition(
                id: new CardId('buk36'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Networking',
                description: 'Baue deine Geschäftsbeziehungen sorgfältig aus, um deine berufliche Laufbahn zu fördern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk37" => new KategorieCardDefinition(
                id: new CardId('buk37'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Organisation Technik-Camp',
                description: 'Unterstütze eine Schule, indem du ein Technik-Camp für Jugendliche organisierst. Dieses Engagement kannst du dir im Lebenslauf anrechnen lassen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk38" => new KategorieCardDefinition(
                id: new CardId('buk38'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Politik-News',
                description: 'Um dich politisch und wirtschaftlich auf dem Laufenden zu halten, abonnierst du eine individuell zusammengestellte Auswahl wichtiger Berichte und Reportagen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk39" => new KategorieCardDefinition(
                id: new CardId('buk39'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Außendarstellung',
                description: 'Stärke deinen Auftritt, indem du deine Bewerbungsunterlagen und deine Onlinepräsenz professionell optimieren lässt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk40" => new KategorieCardDefinition(
                id: new CardId('buk40'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Verkaufscoaching',
                description: 'Verkaufen ist eine Schlüsselkompetenz, ob im Vorstellungsgespräch oder im Gespräch mit dem Stromanbieter. Wer die eigenen Ideen überzeugend präsentiert, kommt schneller ans Ziel. Deshalb investierst du 15.000 € in ein Verkaufscoaching.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk41" => new KategorieCardDefinition(
                id: new CardId('buk41'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sommerfest',
                description: 'Du lädst deine Kollegschaft zu einem Sommerfest in deine private Villa am See ein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk42" => new KategorieCardDefinition(
                id: new CardId('buk42'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Auslandsaufenthalt',
                description: 'Im Rahmen deiner Karriereplanung absolvierst du einen sechsmonatigen Aufenthalt in Shanghai.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk43" => new KategorieCardDefinition(
                id: new CardId('buk43'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Internationale Netzwerke',
                description: 'Werde Teil beruflich relevanter Netzwerke und nutze internationale Konferenzen, um dich fachlich und persönlich weiterzuentwickeln.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-60000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk44" => new KategorieCardDefinition(
                id: new CardId('buk44'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder absolvierten Weiterbildung wächst nicht nur dein Wissen – auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk45" => new KategorieCardDefinition(
                id: new CardId('buk45'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kongresseinladung',
                description: 'Du wirst eingeladen, auf einem wichtigen Kongress über deine berufliche Erfahrung zu sprechen. Die Vorbereitung der Rede nimmt viel Zeit in Anspruch, macht dich aber sehr bekannt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk46" => new KategorieCardDefinition(
                id: new CardId('buk46'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Karriere-Booster: Französisch',
                description: 'Nutze die schönste Sprache der Welt für dich: Bei einem längeren Aufenthalt in Paris bekommst du die Gelegenheit, dein Schul-Französisch aufzufrischen und weiterzuentwickeln.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk47" => new KategorieCardDefinition(
                id: new CardId('buk47'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mitgliedschaft Kulturverein',
                description: 'Schließe eine Mitgliedschaft im Kulturverein ab und triff dich mit Gleichgesinnten in den Opern dieser Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk48" => new KategorieCardDefinition(
                id: new CardId('buk48'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Austauschprogramm',
                description: 'Du nimmst an einem Austauschprogramm teil und arbeitest zwei Monate in einem fremden Unternehmen. Dafür opferst du einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk49" => new KategorieCardDefinition(
                id: new CardId('buk49'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Wertekongress',
                description: 'Du nimmst an einem Wertekongress für Führungskräfte teil und investierst 3.000 € in die Teilnahme.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk50" => new KategorieCardDefinition(
                id: new CardId('buk50'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mehr Erfolg durch Fitness',
                description: 'Regelmäßiger Sport hält dich fit und kann dir auch im Beruf helfen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk51" => new KategorieCardDefinition(
                id: new CardId('buk51'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Internationaler Kongress',
                description: 'Du wirst eingeladen, bei einem internationalen Kongress zu sprechen. Die Reisekosten musst du allerdings selbst übernehmen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk52" => new KategorieCardDefinition(
                id: new CardId('buk52'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Organisation KI-Camp',
                description: 'Unterstütze eine Schule durch die Organisation eines KI-Camps. Dein Engagement kannst du dir im Lebenslauf notieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk53" => new KategorieCardDefinition(
                id: new CardId('buk53'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du absolvierst eine Weiterbildung an der Abendschule. Mit jeder absolvierten Weiterbildung wächst nicht nur dein Wissen – auch deine beruflichen Perspektiven erweitern sich.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk54" => new KategorieCardDefinition(
                id: new CardId('buk54'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Ernährungsumstellung',
                description: 'Du bezahlst einen Ernährungs- und Fitnesscoach, der dich fit macht, um voller Power und Energie die nächste Stufe der Karriereleiter zu erklimmen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk55" => new KategorieCardDefinition(
                id: new CardId('buk55'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Wertekongress',
                description: 'Du nimmst an einem Wertekongress für Führungskräfte teil und investierst 6.000 € in die Teilnahme.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk56" => new KategorieCardDefinition(
                id: new CardId('buk56'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Teambuildingworkshop',
                description: 'Du lädst dein Team zu einem Teambuilding-Workshop auf deine private Finca nach Mallorca ein und übernimmst sämtliche Reise- und Verpflegungskosten. Das Ergebnis: ein gestärktes Miteinander und deutlich steigende Umsatzzahlen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk57" => new KategorieCardDefinition(
                id: new CardId('buk57'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Austauschprogramm',
                description: 'Du nimmst an einem Austauschprogramm teil und arbeitest sechs Monate in einem fremden Unternehmen. Dafür opferst du einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk58" => new KategorieCardDefinition(
                id: new CardId('buk58'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Tagesseminar',
                description: 'Positionierung ist das A und O. Deshalb besuchst du ein Tagesseminar, in dem du lernst, dich in deiner Branche gezielt zu positionieren.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk59" => new KategorieCardDefinition(
                id: new CardId('buk59'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kongresseinladung',
                description: 'Du wirst eingeladen, auf einem wichtigen Kongress über deine berufliche Erfahrung zu sprechen. Die Vorbereitung der Rede nimmt viel Zeit in Anspruch, macht dich aber sehr bekannt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk60" => new KategorieCardDefinition(
                id: new CardId('buk60'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Auslandsaufenthalt',
                description: 'Du wagst den nächsten Karriereschritt und ziehst für zwei Jahre nach Hongkong.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk61" => new KategorieCardDefinition(
                id: new CardId('buk61'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Du wolltest schon immer dein kreatives Talent fördern und belegst eine Weiterbildung in Kunstgeschichte.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk62" => new KategorieCardDefinition(
                id: new CardId('buk62'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'BAföG-Schulden',
                description: 'Schließe dein Studium endlich erfolgreich ab! Zahle deine BAföG-Schulden in Höhe von 10.000 € zurück.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk63" => new KategorieCardDefinition(
                id: new CardId('buk63'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Pre-Master-Programm',
                description: 'Absolviere ein Pre-Master-Programm, um optimal vorbereitet zu starten und deine Chancen auf sehr gute Noten im Masterstudium zu erhöhen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-800),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk64" => new KategorieCardDefinition(
                id: new CardId('buk64'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Wöchentlicher Podcast',
                description: 'Nimm dir ein Beispiel an erfolgreichen CEOs: Höre dir jede Woche einen neuen Podcast an und fasse die wichtigsten Erkenntnisse anschließend zusammen. So stärkst du kontinuierlich dein Wissen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-900),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk65" => new KategorieCardDefinition(
                id: new CardId('buk65'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Längere Reise',
                description: 'Unternimm eine längere Reise in ein dir unbekanntes Land, um dich kulturell weiterzubilden.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8500),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk66" => new KategorieCardDefinition(
                id: new CardId('buk66'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Onlinekurs Universität',
                description: 'Viele Universitäten stellen ihre Vorlesungen mittlerweile online zur Verfügung. Profitiere davon und belege einen Kurs einer amerikanischen Eliteuniversität.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk67" => new KategorieCardDefinition(
                id: new CardId('buk67'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Trainerschein als Fluglehrkraft',
                description: 'Erfülle dir deinen Traum und mache deinen Trainerschein als Fluglehrkraft. Neben technischem Wissen eignest du dir Kenntnisse über aerodynamische Prozesse an.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-35000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk69" => new KategorieCardDefinition(
                id: new CardId('buk69'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachkurs',
                description: 'Absolviere einen dreimonatigen Sprachkurs im Ausland.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk70" => new KategorieCardDefinition(
                id: new CardId('buk70'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Zeitungsabonnement',
                description: 'Informiere dich jeden Morgen über aktuelle Ereignisse in der Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk71" => new KategorieCardDefinition(
                id: new CardId('buk71'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kulturmäzen',
                description: 'Engagiere dich als Kulturmäzen deiner Stadt und besuche regelmäßig interessante Premieren, die mit deinem Sponsoring ermöglicht wurden.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk72" => new KategorieCardDefinition(
                id: new CardId('buk72'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Programmierkurs',
                description: 'Besuche einen Programmierkurs in der Abendschule, um dich für die Digitalisierung fit zu machen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk73" => new KategorieCardDefinition(
                id: new CardId('buk73'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Jagdschein',
                description: 'Verwirkliche deinen Traum und erwirb einen Jagdschein. Dabei lernst du nicht nur viel über Flora und Fauna, sondern eignest dir auch geografische Kenntnisse an.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-17000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk74" => new KategorieCardDefinition(
                id: new CardId('buk74'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Gedächtnistraining',
                description: 'Mache jeden Tag 10 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk75" => new KategorieCardDefinition(
                id: new CardId('buk75'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachreise',
                description: 'Absolviere eine Sprachreise, um deine Kommunikationsfähigkeiten zu verbessern. Du wirst dabei von einer kompetenten Sprachlehrkraft optimal und individuell betreut.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk76" => new KategorieCardDefinition(
                id: new CardId('buk76'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Coach Strategieentwicklung',
                description: 'Engagiere einen Coach, der dich in deiner Strategieentwicklung unterstützt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-56000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk77" => new KategorieCardDefinition(
                id: new CardId('buk77'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Weiterbildung',
                description: 'Schließe deine berufsbegleitende Weiterbildung endlich erfolgreich ab.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk78" => new KategorieCardDefinition(
                id: new CardId('buk78'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'MBA',
                description: 'Eigne dir betriebswirtschaftliche Kenntnisse durch einen MBA an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-45000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk79" => new KategorieCardDefinition(
                id: new CardId('buk79'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mentorenprogramm',
                description: 'Bewirb dich für ein Mentorenprogramm und nutze die Chance, dich wöchentlich mit deinem Mentor, einem bekannten CEO, beim Mittagessen auszutauschen. Aus Dankbarkeit für seine Zeit übernimmst du regelmäßig die Einladung zum Mittagessen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-77000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk80" => new KategorieCardDefinition(
                id: new CardId('buk80'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Gedächtnistraining',
                description: 'Mache jeden Tag 10 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk81" => new KategorieCardDefinition(
                id: new CardId('buk81'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Briefing',
                description: 'Lass dir individuelle Briefings zum aktuellen Weltgeschehen zusenden, genau auf deine Bedürfnisse abgestimmt und vollgepackt mit den Informationen, die wirklich wichtig für dich sind.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-47000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk82" => new KategorieCardDefinition(
                id: new CardId('buk82'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kulturmäzen',
                description: 'Engagiere dich als Kulturmäzen deiner Stadt und besuche nun regelmäßig interessante Premieren, die mit deinem Sponsoring ermöglicht wurden.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-60000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk83" => new KategorieCardDefinition(
                id: new CardId('buk83'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Archäologische Expedition',
                description: 'Mache eine archäologische Expedition, um deine Abenteuerlust zu stillen. Du wirst dabei von einem kompetenten Guide optimal und individuell betreut.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-90000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk84" => new KategorieCardDefinition(
                id: new CardId('buk84'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Sprachbegleitung',
                description: 'Hol dir professionelle Sprachbegleitung für verhandlungssichere Auftritte im internationalen Umfeld.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-32000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk85" => new KategorieCardDefinition(
                id: new CardId('buk85'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'MBA',
                description: 'Bereite dich auf deinen MBA-Abschluss optimal vor und investiere in einen kommerziellen Kurs zur Prüfungsvorbereitung.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-32000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk86" => new KategorieCardDefinition(
                id: new CardId('buk86'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Kurs Töpferei',
                description: 'Du wolltest schon immer dein kreatives Talent fördern und belegst einen Anfänger- und einen Aufbaukurs in Töpferei.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk87" => new KategorieCardDefinition(
                id: new CardId('buk87'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Onlinekurs Universität',
                description: 'Immer mehr Universitäten bieten ihre Vorlesungen online an. Nutze diese Chance und belege einen Kurs an einer renommierten britischen Eliteuniversität.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-35000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk88" => new KategorieCardDefinition(
                id: new CardId('buk88'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Bekannte Persönlichkeiten',
                description: 'Lade regelmäßig inspirierende und bekannte Persönlichkeiten ein und lerne aus erster Hand von ihrer Erfahrung und ihrem Werdegang.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-83000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk89" => new KategorieCardDefinition(
                id: new CardId('buk89'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'Mentoringnetzwerk',
                description: 'Baue ein Mentoringnetzwerk für die jüngere Generation deiner Branche auf. Dabei eignest du dir viel zusätzliches Wissen an.',
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
                title: 'Bergtourleitung',
                description: 'Erfülle dir deinen Traum und bilde dich zur Bergtourleitung aus. Neben technischem Wissen eignest du dir geografische und pädagogische Kenntnisse an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    zeitsteineChange: -1,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk91" => new KategorieCardDefinition(
                id: new CardId('buk91'),
                categoryId: CategoryId::BILDUNG_UND_KARRIERE,
                title: 'TED-Talks',
                description: 'Reise zu den großen TED-Talks, um immer auf dem neuesten Stand wichtiger Innovationen zu bleiben.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-68000),
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "suf1" => new KategorieCardDefinition(
                id: new CardId('suf1'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Beeinträchtigungen ermöglicht, einen erholsamen Urlaub am Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
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
                title: 'Teilnahme Spendenmarathon',
                description: 'Du nimmst an einem Spendenmarathon für krebskranke Kinder teil. Die Suche nach Sponsoren kostet dich jedoch Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf3" => new KategorieCardDefinition(
                id: new CardId('suf3'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Besuch Pflegeheim',
                description: 'Du besuchst eine Pflegeeinrichtung und veranstaltest mit den Bewohnenden einen Brettspielabend.',
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
                title: 'Interventionsprogramm',
                description: 'Du organisierst ein Interventionsprogramm gegen häusliche Gewalt. Dafür benötigst du jedoch einen Veranstaltungsraum und einen Moderationskoffer, für die du selbst aufkommen musst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf5" => new KategorieCardDefinition(
                id: new CardId('suf5'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft für eine geflüchtete Person',
                description: 'Du übernimmst eine Patenschaft für eine geflüchtete Person.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf6" => new KategorieCardDefinition(
                id: new CardId('suf6'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf7" => new KategorieCardDefinition(
                id: new CardId('suf7'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du immer Tiernahrung für die umliegenden Tierheime. Dein Spendenbeitrag beträgt 200 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf8" => new KategorieCardDefinition(
                id: new CardId('suf8'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Projektteilnahme',
                description: 'Du nimmst an einem 500-Euro-Projekt teil. Ziel ist es, 500 € auf kreative Art und Weise zu verdienen und diese an ein Projekt deiner Wahl zu spenden. #füreinebessereWelt',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf9" => new KategorieCardDefinition(
                id: new CardId('suf9'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Der örtliche Turnverein sucht noch händeringend nach Unterstützung. Du erklärst dich bereit, ehrenamtlich als Betreuungsperson beim Mutter-Kind-Turnen auszuhelfen. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf10" => new KategorieCardDefinition(
                id: new CardId('suf10'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem',
                description: 'Du bildest ein Sprachtandem mit einem Erasmus-Studierenden und lernst dabei viel über Sprachen und fremde Kulturen. Das kostet dich jedoch einen Zeitstein.',
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
                title: 'Brief an Regierung',
                description: 'Regieren ist kein einfacher Job. Die Presse schießt zunehmend gegen das Kanzleramt. Du nimmst dir Zeit und schreibst einen ermutigenden Brief an das Regierungsoberhaupt und schickst ihm einige Köstlichkeiten zur Stärkung nach Berlin.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-150),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf12" => new KategorieCardDefinition(
                id: new CardId('suf12'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Kostenlose Nachhilfe',
                description: 'Du gibst Nachhilfe für sozial benachteiligte Kinder. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf13" => new KategorieCardDefinition(
                id: new CardId('suf13'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spazieren mit Hunden',
                description: 'Du hilfst dem örtlichen Tierheim und gehst dreimal wöchentlich mit Hunden spazieren. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf14" => new KategorieCardDefinition(
                id: new CardId('suf14'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Du spendest einmalig 1.000 € an eine gemeinnützige Bildungsinitiative.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf15" => new KategorieCardDefinition(
                id: new CardId('suf15'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Kleidertauschparty',
                description: 'Du sortierst deinen Kleiderschrank aus und nimmst an einer Kleidertauschparty teil.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf16" => new KategorieCardDefinition(
                id: new CardId('suf16'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Du spendest einmalig 1.000 € an eine Umweltschutzorganisation.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf17" => new KategorieCardDefinition(
                id: new CardId('suf17'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Staubsauger-Roboter',
                description: 'Du kaufst einen Staubsauger-Roboter – nie wieder die Wohnung saugen!',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf18" => new KategorieCardDefinition(
                id: new CardId('suf18'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Haushaltshilfe',
                description: 'Du engagierst eine Haushaltshilfe, um mehr Zeit für dich zu haben.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf19" => new KategorieCardDefinition(
                id: new CardId('suf19'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Musikverein',
                description: 'Du meldest dich im Musikverein an. Dafür musst du ein teures Instrument kaufen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf20" => new KategorieCardDefinition(
                id: new CardId('suf20'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Bio-Essen',
                description: 'Du hast keine Lust mehr zu kochen, daher lässt du dir lieber hervorragendes Bio-Essen liefern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf21" => new KategorieCardDefinition(
                id: new CardId('suf21'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Volleyballverein',
                description: 'Du meldest dich im Volleyballverein an und wirst Teil einer aktiven Mannschaft.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf22" => new KategorieCardDefinition(
                id: new CardId('suf22'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fitness',
                description: 'Du meldest dich im Fitnessstudio an und nimmst dir die Zeit, dreimal wöchentlich mit einem Personaltrainer zu trainieren. Im kommenden Jahr bist du viel seltener krank.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf23" => new KategorieCardDefinition(
                id: new CardId('suf23'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Reinigungskraft',
                description: 'Du möchtest mehr Zeit mit deinen Liebsten verbringen und entscheidest dich für eine Reinigungskraft, die deine Wohnung zukünftig sauber hält.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf24" => new KategorieCardDefinition(
                id: new CardId('suf24'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schlaftracking',
                description: 'Du stattest dein Schlafzimmer mithilfe neuester Erkenntnisse aus der Schlafforschung aus. Dein Schlaf ist nun deutlich tiefer und du bist tagsüber viel erholter.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf25" => new KategorieCardDefinition(
                id: new CardId('suf25'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fußballverein',
                description: 'Du meldest dich in einem Fußballverein an, um dich fit zu halten. Dafür benötigst du hochwertige Sportkleidung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf26" => new KategorieCardDefinition(
                id: new CardId('suf26'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pflegedienst',
                description: 'Du beauftragst einen Pflegedienst zur Pflege deiner Großeltern, um mehr Zeit für dich zu haben.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf27" => new KategorieCardDefinition(
                id: new CardId('suf27'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Kantine',
                description: 'Da du nicht mehr kochen möchtest, nutzt du nur noch die Essensangebote in der Kantine.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-300),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf28" => new KategorieCardDefinition(
                id: new CardId('suf28'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Hausverwaltung',
                description: 'Du engagierst eine Hausverwaltung für deine Mietwohnung, um mehr Zeit für dich zu haben.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf29" => new KategorieCardDefinition(
                id: new CardId('suf29'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fundraisingaktion',
                description: 'Du organisierst eine Fundraisingaktion für die Nothilfe nach Naturkatastrophen. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf30" => new KategorieCardDefinition(
                id: new CardId('suf30'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Petition aufsetzen',
                description: 'Du setzt eine Petition auf, die sich für das Bleiberecht für Geflüchtete in Deutschland einsetzt. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf31" => new KategorieCardDefinition(
                id: new CardId('suf31'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Du übernimmst einen Vorstandsposten im Tennisverein. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf32" => new KategorieCardDefinition(
                id: new CardId('suf32'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für Demokratie',
                description: 'Du setzt einen Informationsflyer auf, der über die demokratischen Werte informiert. Der Druck des Flyers kostet dich 500 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf33" => new KategorieCardDefinition(
                id: new CardId('suf33'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Webseite zur Nachbarschaftshilfe',
                description: 'Du setzt eine Webseite auf, die es ermöglicht, sich in der Nachbarschaftshilfe zu engagieren. Das Hosten der Webseite kostet dich 400 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-400),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf34" => new KategorieCardDefinition(
                id: new CardId('suf34'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sterbebegleitung',
                description: 'Du entscheidest dich, einen ehrenamtlichen Kurs zur Sterbebegleitung zu absolvieren. Dies kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf35" => new KategorieCardDefinition(
                id: new CardId('suf35'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Valentinstag',
                description: 'Obwohl du am Valentinstag Single bist, entscheidest du dich dazu, jedem glücklichen Paar, das dir heute über den Weg läuft, eine Rose zu schenken.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-80),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf36" => new KategorieCardDefinition(
                id: new CardId('suf36'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Aufklärung Menschenhandel',
                description: 'Du gehst auf die Straße und klärst Menschen über moderne Sklaverei auf. Menschenhandel gehört leider nicht der Vergangenheit an, sondern ist traurige Gegenwart. Dafür kaufst du Plakate und Flyer.',
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
                title: 'Arbeit im Eine-Welt-Laden',
                description: 'Du entscheidest dich, ehrenamtlich jede Woche im Eine-Welt-Laden zu arbeiten, der fair gehandelte Produkte verkauft.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
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
                title: 'Clean-up-Aktion',
                description: 'Du machst bei einer Clean-up-Aktion in deiner Stadt mit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf40" => new KategorieCardDefinition(
                id: new CardId('suf40'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Stipendium in Indonesien',
                description: 'Du übernimmst die Stipendienkosten für zwei Geschwister in Indonesien.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf41" => new KategorieCardDefinition(
                id: new CardId('suf41'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Nachbarschaftsgarten',
                description: 'Du engagierst dich für einen gemeinschaftlichen Nachbarschaftsgarten, in dem sich die Bewohnenden Ernte, Kosten und Risiken teilen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(1),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-200),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf42" => new KategorieCardDefinition(
                id: new CardId('suf42'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Interventionsprogramm',
                description: 'Du organisierst ein Interventionsprogramm gegen häusliche Gewalt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf43" => new KategorieCardDefinition(
                id: new CardId('suf43'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Geschenke Obdachlose',
                description: 'Du verteilst Nikolausgeschenke an Obdachlose in deiner Stadt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf44" => new KategorieCardDefinition(
                id: new CardId('suf44'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Besuch Pflegeheim',
                description: 'Du besuchst eine Pflegeeinrichtung und veranstaltest mit den Bewohnenden einen Brettspielabend.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf45" => new KategorieCardDefinition(
                id: new CardId('suf45'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Mentoring an der Universität',
                description: 'Du engagierst dich an der Universität und unterstützt junge Absolventinnen und Absolventen beim Berufseinstieg.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf46" => new KategorieCardDefinition(
                id: new CardId('suf46'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Charity-Event',
                description: 'Du organisierst ein Charity-Event zugunsten benachteiligter Menschen in Niger.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf47" => new KategorieCardDefinition(
                id: new CardId('suf47'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Du spendest einmalig 2.000 € für einen wohltätigen Zweck.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf48" => new KategorieCardDefinition(
                id: new CardId('suf48'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sponsoring',
                description: 'Du engagierst dich als Sponsor eines Charity-Events für krebskranke Kinder.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf49" => new KategorieCardDefinition(
                id: new CardId('suf49'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Aufgrund deiner Liebe zu Tieren entscheidest du dich dazu, die lokalen Tierheime zu unterstützen. Dafür spendest du 8.000 €.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf50" => new KategorieCardDefinition(
                id: new CardId('suf50'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Mathenachhilfe',
                description: 'Du gibst kostenlose Mathenachhilfe für Kinder in deinem Viertel. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf51" => new KategorieCardDefinition(
                id: new CardId('suf51'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft',
                description: 'Du übernimmst die Patenschaft für drei Kinder in Moldawien und kümmerst dich um ihre Schulbildung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf52" => new KategorieCardDefinition(
                id: new CardId('suf52'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spazieren mit Hunden',
                description: 'Du hilfst dem örtlichen Tierheim und gehst dreimal wöchentlich mit Hunden spazieren. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf53" => new KategorieCardDefinition(
                id: new CardId('suf53'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem Jugendzentrum eines Brennpunktviertels. Die Arbeit macht Spaß, erfordert aber viel Zeit. Du verlierst einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf54" => new KategorieCardDefinition(
                id: new CardId('suf54'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendenmarathon',
                description: 'Du nimmst an einem Spendenmarathon für krebskranke Kinder teil. Die Suche nach Sponsoren kostet Zeit. Du verlierst einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf55" => new KategorieCardDefinition(
                id: new CardId('suf55'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du fliegst nach Bangladesch, um sechs Monate Englisch an einer neuen Schule zu unterrichten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf56" => new KategorieCardDefinition(
                id: new CardId('suf56'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schule Fidschi',
                description: 'Du nutzt deinen Jahresurlaub, um eine Schule auf Fidschi aufzubauen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf57" => new KategorieCardDefinition(
                id: new CardId('suf57'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Musikkurs',
                description: 'Du meldest dich in einem neuen Musikkurs an, um deine Fähigkeiten zu verbessern und neue Kontakte zu knüpfen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7600),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf58" => new KategorieCardDefinition(
                id: new CardId('suf58'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem',
                description: 'Du bildest ein regelmäßiges Sprachtandem mit deiner neuen Nachbarin aus Spanien und lernst viel über die Sprache und die fremde Kultur.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf59" => new KategorieCardDefinition(
                id: new CardId('suf59'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Du spendest einmalig 5.000 € für einen wohltätigen Zweck.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf60" => new KategorieCardDefinition(
                id: new CardId('suf60'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spazieren mit Hunden',
                description: 'Du hilfst dem örtlichen Tierheim und gehst dreimal wöchentlich mit Hunden spazieren. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf61" => new KategorieCardDefinition(
                id: new CardId('suf61'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendenmarathon',
                description: 'Du nimmst an einem Spendenmarathon für krebskranke Kinder teil. Die Suche nach Sponsoren kostet dich jedoch Zeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf62" => new KategorieCardDefinition(
                id: new CardId('suf62'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Seniorenzentrum. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf63" => new KategorieCardDefinition(
                id: new CardId('suf63'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Sponsoring',
                description: 'Du engagierst dich als Sponsor einer Ferienfreizeit für Kinder, die in einem Hospiz leben.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf64" => new KategorieCardDefinition(
                id: new CardId('suf64'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Aufgrund deiner Liebe zu Tieren entscheidest du dich dazu, die lokalen Tierheime zu unterstützen. Dafür spendest du 10.000 €.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf65" => new KategorieCardDefinition(
                id: new CardId('suf65'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Geschenke Obdachlose',
                description: 'Du verteilst Ostergeschenke an obdachlose Menschen in deiner Stadt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf66" => new KategorieCardDefinition(
                id: new CardId('suf66'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Besuch Pflegeheim',
                description: 'Du besuchst eine Pflegeeinrichtung und veranstaltest mit den Bewohnenden einen Brettspielabend.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf67" => new KategorieCardDefinition(
                id: new CardId('suf67'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich in einem Haus für Menschen mit geistiger Behinderung. Dies kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf68" => new KategorieCardDefinition(
                id: new CardId('suf68'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Berufsorientierung am Gymnasium',
                description: 'Du engagierst dich an einem Gymnasium und unterstützt Schülerinnen und Schüler bei der Berufsorientierung.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf69" => new KategorieCardDefinition(
                id: new CardId('suf69'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Du spendest einmalig 10.000 € für einen wohltätigen Zweck.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf70" => new KategorieCardDefinition(
                id: new CardId('suf70'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft',
                description: 'Du übernimmst die Patenschaft für drei Kinder in Moldawien und schreibst ihnen regelmäßig.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-6000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf71" => new KategorieCardDefinition(
                id: new CardId('suf71'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Babysitten',
                description: 'Du hilfst jungen Paaren kostenlos beim Babysitten aus, damit sie wieder mehr Zeit haben, um ihre Beziehung zu pflegen. Dies kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf72" => new KategorieCardDefinition(
                id: new CardId('suf72'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. Dies kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf73" => new KategorieCardDefinition(
                id: new CardId('suf73'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Charity-Event',
                description: 'Du organisierst ein Charity-Event zugunsten benachteiligter Menschen in Niger.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-70000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf74" => new KategorieCardDefinition(
                id: new CardId('suf74'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Spende',
                description: 'Du spendest einmalig 5.000 € für einen wohltätigen Zweck.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf75" => new KategorieCardDefinition(
                id: new CardId('suf75'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Treffen mit Mitbewohnenden',
                description: 'Du triffst dich regelmäßig mit deinen neuen Mitbewohnenden aus dem Iran zum Abendessen. Bei gutem Essen und langen Gesprächen lernst du viel über die dir fremde Kultur.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf76" => new KategorieCardDefinition(
                id: new CardId('suf76'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Jahresurlaub',
                description: 'Du nutzt deinen Jahresurlaub, um eine Schule nach einem Erdbeben aufzubauen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf77" => new KategorieCardDefinition(
                id: new CardId('suf77'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Unterricht Waisenheim',
                description: 'Du gehst für sechs Monate nach Tansania, um Englisch in einem Waisenheim zu unterrichten.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf78" => new KategorieCardDefinition(
                id: new CardId('suf78'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Teilnahme Spendenmarathon',
                description: 'Du nimmst an einem Spendenmarathon für krebskranke Kinder teil. Die Suche nach Sponsoren kostet dich jedoch Zeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf79" => new KategorieCardDefinition(
                id: new CardId('suf79'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Steuerberaterin',
                description: 'Deine Unterlagen der letzten Jahre wachsen dir über den Kopf. Deshalb engagierst du eine Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf80" => new KategorieCardDefinition(
                id: new CardId('suf80'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- und Steuerberaterin',
                description: 'Auf Empfehlung engagierst du eine kompetente Finanz- und Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf81" => new KategorieCardDefinition(
                id: new CardId('suf81'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Einkäufe',
                description: 'Du entscheidest dich dazu, dir deine Einkäufe an die Haustüre zu liefern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf83" => new KategorieCardDefinition(
                id: new CardId('suf83'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pflegedienst',
                description: 'Du engagierst einen Pflegedienst für deine Großeltern und nutzt die gemeinsame Zeit für Spaziergänge und Kaffeenachmittage.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf84" => new KategorieCardDefinition(
                id: new CardId('suf84'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Putzdienst',
                description: 'Du engagierst einen kompetenten Reinigungsdienst für dein Chaos.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf85" => new KategorieCardDefinition(
                id: new CardId('suf85'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Familienreise',
                description: 'Du buchst eine Reise für dich und deine Familie nach Irland. Du genießt mit deiner Familie die erholsame und ruhige Zeit in der Natur.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-34000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf86" => new KategorieCardDefinition(
                id: new CardId('suf86'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schlaftracking',
                description: 'Du stattest dein Schlafzimmer mithilfe neuester Erkenntnisse aus der Schlafforschung aus. Dein Schlaf ist nun deutlich tiefer und du bist tagsüber viel erholter.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9800),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf87" => new KategorieCardDefinition(
                id: new CardId('suf87'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Plastikfreier Haushalt',
                description: 'Du stellst auf einen plastikfreien Haushalt um. Seit du konsequent auf plastikfreie Alternativen setzt, hast du das Gefühl, bewusster zu leben und schläfst mit einem ruhigeren Gewissen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-11000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf88" => new KategorieCardDefinition(
                id: new CardId('suf88'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Einkäufe',
                description: 'Du gehst nicht mehr selbst einkaufen – stattdessen lässt du dir deine Einkäufe per Drohne nach Hause liefern. So bleibt dir mehr Zeit für dich und deine Freunde.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf89" => new KategorieCardDefinition(
                id: new CardId('suf89'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Steuerberaterin',
                description: 'Deine Unterlagen vom letzten Jahr wachsen dir über den Kopf. Deshalb engagierst du eine Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf90" => new KategorieCardDefinition(
                id: new CardId('suf90'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Dauerkarte 1. Liga',
                description: 'Verpasse kein Spiel mehr: Du kaufst dir eine Dauerkarte für deinen Lieblingsverein und fährst zu jedem Heimspiel. Du musst 7.000 € Kosten für die Dauerkarte und An- und Abreise zu den Spielen zahlen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf91" => new KategorieCardDefinition(
                id: new CardId('suf91'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- und Steuerberaterin',
                description: 'Auf Empfehlung engagierst du eine kompetente Finanz- und Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8700),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf92" => new KategorieCardDefinition(
                id: new CardId('suf92'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Au-pair',
                description: 'Du engagierst eine Au-pair-Betreuung und genießt die neue Freizeit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf93" => new KategorieCardDefinition(
                id: new CardId('suf93'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Persönliche Stylistin',
                description: 'Du stellst deine persönliche Stylistin ein. Du ersparst dir dadurch lästige Shopping-Touren.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf94" => new KategorieCardDefinition(
                id: new CardId('suf94'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Persönliche Assistentin',
                description: 'Du stellst eine persönliche Assistentin ein, die dich bei deinen beruflichen Aufgaben unterstützt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-7500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf95" => new KategorieCardDefinition(
                id: new CardId('suf95'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fitness',
                description: 'Du meldest dich in einem Fitnessstudio an und trainierst dreimal wöchentlich mit einem Personaltrainer. Daher bist du im kommenden Jahr seltener krank.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-9000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf96" => new KategorieCardDefinition(
                id: new CardId('suf96'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Betreuung Buchhaltung',
                description: 'Du beauftragst ein Unternehmen mit der Betreuung deiner Buchhaltung und deiner Termine. So bleibt dir mehr Zeit für die wichtigen Dinge.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf97" => new KategorieCardDefinition(
                id: new CardId('suf97'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Privatköchin',
                description: 'Deine Kochfähigkeiten sind miserabel und die Küche ist danach immer ein Schlachtfeld. Daher engagierst du eine Privatköchin für dich und deine Liebsten.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-43000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf98" => new KategorieCardDefinition(
                id: new CardId('suf98'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Whirlpool',
                description: 'Du lässt dir einen Whirlpool in den Garten bauen, um nach der Arbeit gemütlich entspannen zu können. Du erhältst jetzt überraschenderweise viel mehr Besuch von deiner Nachbarschaft.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf99" => new KategorieCardDefinition(
                id: new CardId('suf99'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Privatsauna',
                description: 'Du lässt dir eine Privatsauna als Ausgleich vom stressigen Alltag bauen. Die Auszeit mit deinen Freunden tut dir gut. Das kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-50000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf100" => new KategorieCardDefinition(
                id: new CardId('suf100'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Jahresabo Bio-Essen',
                description: 'Du hast keine Lust mehr zu kochen, daher lässt du dir hervorragendes Bio-Essen im Jahresabo zur Arbeit und direkt in deine Wohnung liefern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-14500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf101" => new KategorieCardDefinition(
                id: new CardId('suf101'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Markt',
                description: 'Du kaufst nur noch auf dem Markt ein, um dich gesünder zu ernähren. Dadurch fühlst du dich gut und fit.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf102" => new KategorieCardDefinition(
                id: new CardId('suf102'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Dauerkarte 1. Liga',
                description: 'Verpasse kein Spiel mehr: Du kaufst dir eine VIP-Dauerkarte für deinen Favoriten in der ersten Liga und fährst zu jedem Heimspiel. Zusammen mit den Übernachtungen vor Ort kostet dich das 30.000 €.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf103" => new KategorieCardDefinition(
                id: new CardId('suf103'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Schwimmbad',
                description: 'Du lässt dir ein privates, kleines Schwimmbad als Ausgleich vom stressigen Alltag bauen. Die Auszeit mit deinen Freunden wird dir helfen. Du verlierst einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-78000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf104" => new KategorieCardDefinition(
                id: new CardId('suf104'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Finanz- und Steuerberaterin',
                description: 'Auf Empfehlung engagierst du eine kompetente Finanz- und Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf105" => new KategorieCardDefinition(
                id: new CardId('suf105'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Umzug',
                description: 'Du ziehst in eine neue und zentral gelegene Wohnung – lange Anfahrten gehören der Vergangenheit an.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-33000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf106" => new KategorieCardDefinition(
                id: new CardId('suf106'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Pflegedienst',
                description: 'Du engagierst einen Pflegedienst für deine Großeltern und nutzt die gemeinsame Zeit für Spaziergänge und Kaffeenachmittage.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-45000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf107" => new KategorieCardDefinition(
                id: new CardId('suf107'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ferienhaus in Italien',
                description: 'Du kaufst dir ein wunderschönes, kleines Ferienhaus in Italien. Ab jetzt genießt du regelmäßig die Natur, Espresso und ganz viel Pasta.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-97000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf108" => new KategorieCardDefinition(
                id: new CardId('suf108'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Umzug',
                description: 'Du ziehst in eine ruhige Wohnung ein paar Straßen weiter mit einer besseren Dämmung. Dein Schlaf ist nun deutlich tiefer und du bist tagsüber viel erholter.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-33800),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf109" => new KategorieCardDefinition(
                id: new CardId('suf109'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Bergwelt',
                description: 'Für ein paar Wochen ziehst du dich in die einsame und schöne Bergwelt zurück, damit du zur Ruhe kommen kannst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf110" => new KategorieCardDefinition(
                id: new CardId('suf110'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Garten- und Landschaftspflege',
                description: 'Du engagierst eine Garten- und Landschaftspflege für deinen Garten und genießt deine neu gewonnene Freizeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-32000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf111" => new KategorieCardDefinition(
                id: new CardId('suf111'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Steuerberaterin',
                description: 'Deine Unterlagen vom letzten Jahr wachsen dir über den Kopf. Deshalb engagierst du eine Steuerberaterin.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf112" => new KategorieCardDefinition(
                id: new CardId('suf112'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Zweitwohnung Südsee',
                description: 'Du verbringst die triste Jahreszeit nun in einer Zweitwohnung in der Südsee. Die Sonne verschafft dir neuen Auftrieb und deine Aufgaben gehen dir leicht von der Hand.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-66000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf113" => new KategorieCardDefinition(
                id: new CardId('suf113'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fitness',
                description: 'Du legst dir ein eigenes Fitnesszimmer zu, das optimal auf dich ausgerichtet ist und dir hilft, für deine vielen Ziele in Form zu bleiben.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-43000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf114" => new KategorieCardDefinition(
                id: new CardId('suf114'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Privatköchin',
                description: 'Du engagierst eine Privatköchin für dich und deine Liebsten, damit ihr immer mit frischen und gesunden Mahlzeiten versorgt seid.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-52000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf115" => new KategorieCardDefinition(
                id: new CardId('suf115'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Betreuung Buchhaltung',
                description: 'Du beauftragst ein Unternehmen mit der Betreuung deiner Buchhaltung und deiner Termine. So bleibt dir mehr Zeit für die wichtigen Dinge.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf116" => new KategorieCardDefinition(
                id: new CardId('suf116'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Naturteich',
                description: 'Du lässt dir einen Naturteich in den Garten bauen, um nach der Arbeit gemütlich entspannen zu können. Du erhältst jetzt überraschenderweise viel mehr Besuch von deiner Nachbarschaft.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-44000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf117" => new KategorieCardDefinition(
                id: new CardId('suf117'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Analyse Biorhythmus',
                description: 'Du lässt eine aufwändige Analyse deines Biorhythmus erstellen und arbeitest nun viel effektiver und damit zeitsparender.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-44500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf118" => new KategorieCardDefinition(
                id: new CardId('suf118'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Abgestimmte Nahrungsergänzungsmittel',
                description: 'Du bestellst dir ein regelmäßiges Abo für auf deine Körperwerte abgestimmte Nahrungsergänzungsmittel, die dich fit halten sollen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-32000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf119" => new KategorieCardDefinition(
                id: new CardId('suf119'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ergänzung Weinsammlung',
                description: 'Du ergänzt deine Weinsammlung mit einem Einkauf in Napa, Kalifornien. Das Wachstum deines Weinkellers macht dir viel Freude und belebt dich.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-47000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf120" => new KategorieCardDefinition(
                id: new CardId('suf120'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Party',
                description: 'Du planst eine aufwändige Party für dein gesamtes Umfeld, um dich für die Unterstützung zu bedanken. Die Vorbereitung kostet dich viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf121" => new KategorieCardDefinition(
                id: new CardId('suf121'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Lieferung Einkäufe',
                description: 'Du gehst nicht mehr selbst einkaufen. Stattdessen lässt du dir deine Einkäufe per Drohne nach Hause liefern. So bleibt dir mehr Zeit für dich und deine Freunde.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-12000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf122" => new KategorieCardDefinition(
                id: new CardId('suf122'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Arbeit im Eine-Welt-Laden',
                description: 'Du arbeitest ehrenamtlich wöchentlich im Eine-Welt-Laden, der fair gehandelte Produkte verkauft.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf123" => new KategorieCardDefinition(
                id: new CardId('suf123'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Arbeit im Eine-Welt-Laden',
                description: 'Du arbeitest ehrenamtlich wöchentlich im Eine-Welt-Laden, der fair gehandelte Produkte verkauft.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf124" => new KategorieCardDefinition(
                id: new CardId('suf124'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fundraisingaktion',
                description: 'Du organisierst eine Fundraisingaktion für die Nothilfe nach Naturkatastrophen. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf125" => new KategorieCardDefinition(
                id: new CardId('suf125'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Fundraisingaktion',
                description: 'Du organisierst eine Fundraisingaktion für die Nothilfe nach Naturkatastrophen. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf126" => new KategorieCardDefinition(
                id: new CardId('suf126'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Haushaltshilfe',
                description: 'Du engagierst eine Haushaltshilfe.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf127" => new KategorieCardDefinition(
                id: new CardId('suf127'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Haushaltshilfe',
                description: 'Du engagierst eine Haushaltshilfe.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-70000),
                    freizeitKompetenzsteinChange: +2,
                ),
            ),
            "suf128" => new KategorieCardDefinition(
                id: new CardId('suf128'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Patenschaft',
                description: 'Du übernimmst die Patenschaft für drei Geschwister in Moldawien und schreibst ihnen regelmäßig.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf129" => new KategorieCardDefinition(
                id: new CardId('suf129'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Plattform Mitfahrgelegenheit',
                description: 'Du baust eine Plattform für Mitfahrgelegenheiten im ländlichen Bereich auf. Dies kostet dich 1.500 €.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-1500),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf130" => new KategorieCardDefinition(
                id: new CardId('suf130'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Food-Sharing',
                description: 'Du bist beim Food-Sharing aktiv. Dadurch kannst du Geld sparen (+ 1.000 €) und dein soziales Netzwerk ausbauen. Aber es kostet dich viel Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf131" => new KategorieCardDefinition(
                id: new CardId('suf131'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Upcycling',
                description: 'Indem du alte Geräte im Repair-Café aufarbeitest, sparst du dir Geld für neue Anschaffungen (+ 1.000 €). Zusätzlich knüpfst du neue Kontakte. Jedoch kostet dich das Reparieren Zeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(1000),
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf132" => new KategorieCardDefinition(
                id: new CardId('suf132'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Baumpflanzaktion',
                description: 'Du machst bei einer Baumpflanzaktion in deiner Stadt mit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf133" => new KategorieCardDefinition(
                id: new CardId('suf133'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Insektenhotel',
                description: 'Du hilfst beim Bau von Insektenhotels.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf134" => new KategorieCardDefinition(
                id: new CardId('suf134'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Gründung Gemeinschaftsgarten',
                description: 'Du gründest einen Gemeinschaftsgarten und pachtest dafür eine Fläche an, um Blumen, Gemüse und Kräuter anzubauen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf135" => new KategorieCardDefinition(
                id: new CardId('suf135'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Digitale Tauschbörse',
                description: 'Du baust eine digitale Tauschbörse für Gartengeräte und Maschinen auf.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf136" => new KategorieCardDefinition(
                id: new CardId('suf136'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Upcycling',
                description: 'Du organisierst eine Upcycling-Initiative, bei der du mit einer Gruppe von Freiwilligen alte Kleidung zu neuen, modischen Kleidungsstücken umgestaltest.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-4000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf137" => new KategorieCardDefinition(
                id: new CardId('suf137'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Permakultur',
                description: 'Du startest ein Permakultur-Projekt, bei dem du nachhaltige landwirtschaftliche Praktiken förderst und den Boden regenerierst. Du lädst andere dazu ein, dazuzulernen und die Prinzipien der nachhaltigen Landwirtschaft zu übernehmen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf138" => new KategorieCardDefinition(
                id: new CardId('suf138'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Ökologisches Wirtschaftsnetzwerk',
                description: 'Du baust ein Netzwerk von Unternehmen, Landwirten und Konsumenten auf, das sich der Förderung von ökologisch nachhaltigen Wirtschaftsmodellen verschreibt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-25000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf139" => new KategorieCardDefinition(
                id: new CardId('suf139'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Zero-Waste-Modell',
                description: 'Du führst ein Zero-Waste-Modell für deine Stadt ein. Dafür befragst du viele Fachleute, und der Aufbau des Modells erfordert 30.000 € Startkapital.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-30000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf140" => new KategorieCardDefinition(
                id: new CardId('suf140'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Unterstützung nachhaltige Forschung',
                description: 'Du unterstützt mit 40.000 € ein Forschungsprojekt, das Kohlendioxid aus der Atmosphäre entfernt und dauerhaft bindet.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-40000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf141" => new KategorieCardDefinition(
                id: new CardId('suf141'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Aufbau Solarpark',
                description: 'Um deine Stadt klimaneutraler zu gestalten, baust du einen großen Solarpark für 45.000 € am Rande deiner Stadt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-45000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf142" => new KategorieCardDefinition(
                id: new CardId('suf142'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Einkaufsgemeinschaft',
                description: 'Du organisierst eine Einkaufsgemeinschaft in deiner Nachbarschaft. Ihr bestellt gesammelt und lasst umweltschonend liefern.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf143" => new KategorieCardDefinition(
                id: new CardId('suf143'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Du übernimmst einen Vorstandsposten im Tennisverein. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf144" => new KategorieCardDefinition(
                id: new CardId('suf144'),
                categoryId: CategoryId::SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsarbeit in einem Verein',
                description: 'Du übernimmst einen Vorstandsposten im Tennisverein. Dies nimmt viel Zeit in Anspruch und kostet dich einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    zeitsteineChange: -1,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "j1" => new JobCardDefinition(
                id: new CardId('j1'),
                title: 'freiwilliges Praktikum',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Verkaufsfachkraft im Kfz-Bereich',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Kaufmännische Fachkraft im Büromanagement',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Assistenz Geschäftsleitung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                gehalt: new MoneyAmount(+33000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j44" => new JobCardDefinition(
                id: new CardId('j44'),
                title: 'Fachkraft für Logistik',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+32000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 2,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j52" => new JobCardDefinition(
                id: new CardId('j52'),
                title: 'Meisterin im Bäckerhandwerk',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Logistikkoordination',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+37000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j57" => new JobCardDefinition(
                id: new CardId('j57'),
                title: 'Key Account Management',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+50500),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j62" => new JobCardDefinition(
                id: new CardId('j62'),
                title: 'Hochschuldozierende',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Fachkraft für Wirtschaftsprüfung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(2),
                gehalt: new MoneyAmount(+63000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j71" => new JobCardDefinition(
                id: new CardId('j71'),
                title: 'IT-Teamleitung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Unternehmensberatung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Notarassessorin',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Grabungsleitung Archäologie',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+67000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 4,
                    freizeitKompetenzsteine: 0,
                ),
            ),
            "j78" => new JobCardDefinition(
                id: new CardId('j78'),
                title: 'Klinikprofessorin',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(2),
                gehalt: new MoneyAmount(+66000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 3,
                    freizeitKompetenzsteine: 1,
                ),
            ),
            "j79" => new JobCardDefinition(
                id: new CardId('j79'),
                title: 'Steuerberatung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Psychotherapeutin',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Schulamtsleitung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Hochschulprofessur',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Leitung Softwareentwicklung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Raumfahrtpersonal',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Stabsoffizierin',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Partnerin einer Unternehmensberatung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'IT-Bereichsleitung',
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                description: 'Wenn du einen Job hast, kannst du pro Jahr einen Zeitstein weniger setzen.',
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
                title: 'Stadtführungen',
                description: 'Du hast einen Minijob gemacht und bekommst einmalig Gehalt.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(+3500),
                ),
            ),
            "mj6" => new MinijobCardDefinition(
                id: new CardId('mj6'),
                title: 'Aushilfe Fitnessstudio',
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
                title: 'Nachhilfe',
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
                title: 'Haushaltshilfe',
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
                title: 'Auslieferung von Zeitungen',
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
                title: 'Beziehungskrise',
                description: 'Du streitest dich nur noch mit deiner Partnerin und findest deshalb keine Zeit für die Hausarbeit. Den entstandenen Rückstand aufzuholen, kostet viel Zeit.',
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
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('e110'),
                gewichtung: 4,
            ),
            "e2" => new EreignisCardDefinition(
                id: new CardId('e2'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Teilnahme an Coaching-Seminaren',
                description: 'Glückwunsch! Deine Teilnahme an Coaching-Seminaren zahlt sich aus: Du gewinnst bei einem Wettbewerb für junge Führungskräfte den ersten Platz und erhältst eine Finanzspritze von 5.000 € für dein erstes Start-up.',
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
            "e3" => new EreignisCardDefinition(
                id: new CardId('e3'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Herzlichen Glückwunsch! Deine Bewerbung für die Auszeichnung für besondere Prüfungsleistungen war erfolgreich.',
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
            "e4" => new EreignisCardDefinition(
                id: new CardId('e4'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Neue Liebe',
                description: 'Du bist verliebt und vernachlässigst dadurch deine (Lern-)Pflichten. Alles wieder aufzuholen, kostet viel Zeit.',
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
            "e5" => new EreignisCardDefinition(
                id: new CardId('e5'),
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
            "e6" => new EreignisCardDefinition(
                id: new CardId('e6'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stress',
                description: 'Der Druck setzt dir zu, und du schläfst nicht genug. Deshalb nimmst du dir eine Auszeit.',
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
            "e7" => new EreignisCardDefinition(
                id: new CardId('e7'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert – dein Gehalt erhöht sich dieses Jahr um 20 %, das verlangt jedoch mehr Arbeitszeit.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
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
            "e8" => new EreignisCardDefinition(
                id: new CardId('e8'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Leadership-Seminare',
                description: 'Deine Teamleitung erkennt dein Potenzial. Zweimal im Monat besuchst du ein Leadership-Seminar und bekommst dein eigenes Team, um das Gelernte umzusetzen.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
            "e9" => new EreignisCardDefinition(
                id: new CardId('e9'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kündigung',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust kündigst du unüberlegt deinen Job und erhältst dieses Jahr kein Einkommen mehr. Da du keinen Job mehr hast, erhältst du einen zusätzlichen Zeitstein.',
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
            "e10" => new EreignisCardDefinition(
                id: new CardId('e10'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Mit Vitamin B die Karriere ankurbeln',
                description: 'Dein Onkel lässt seine Beziehungen spielen, und du erhältst eine Beförderung. Dein Gehalt steigt dadurch in diesem Jahr um 20 %.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e11" => new EreignisCardDefinition(
                id: new CardId('e11'),
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
            "e12" => new EreignisCardDefinition(
                id: new CardId('e12'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fachwirtqualifikation (mit Job)',
                description: 'Du entscheidest dich für eine achtmonatige berufsbegleitende Weiterbildung und reduzierst dafür deine Arbeitszeit auf 70 %. Dein Gehalt sinkt entsprechend.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +2,
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
            "e13" => new EreignisCardDefinition(
                id: new CardId('e13'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fachwirtqualifikation (ohne Job)',
                description: 'Du entscheidest dich für eine achtmonatige berufsbegleitende Weiterbildung. Ohne Job bezahlst du sie mit 8.000 € selbst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_NO_JOB,
                ],
                gewichtung: 1,
            ),
            "e14" => new EreignisCardDefinition(
                id: new CardId('e14'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Meisterprüfung (mit Job)',
                description: 'Du absolvierst eine achtmonatige berufsbegleitende Weiterbildung und erwirbst den Meistertitel. Dafür reduzierst du deine Arbeitszeit auf 70 %, dein Gehalt sinkt entsprechend.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +2,
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
            "e15" => new EreignisCardDefinition(
                id: new CardId('e15'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Meisterprüfung (ohne Job)',
                description: 'Du absolvierst eine achtmonatige berufsbegleitende Weiterbildung und erwirbst den Meistertitel. Ohne Job bezahlst du sie mit 8.000 € selbst.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-8000),
                    bildungKompetenzsteinChange: +2,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_NO_JOB,
                ],
                gewichtung: 1,
            ),
            "e16" => new EreignisCardDefinition(
                id: new CardId('e16'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Elite-Internat',
                description: 'Du schickst deine Kinder auf ein Elite-Internat, um ihre beruflichen Chancen zu verbessern. Gleichzeitig knüpfst du bei deinen Besuchen wertvolle Kontakte zu Eltern aus aller Welt.',
                phaseId: LebenszielPhaseId::PHASE_2,
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
            "e17" => new EreignisCardDefinition(
                id: new CardId('e17'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert – dein Gehalt erhöht sich dieses Jahr um 20 %.',
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
            "e18" => new EreignisCardDefinition(
                id: new CardId('e18'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Du hast dich mit deinem Team überworfen. Eine Zusammenarbeit ist nicht mehr möglich. Du kündigst und erhältst ab diesem Jahr kein Einkommen. Aufgrund des Jobverlusts erhältst du einen zusätzlichen Zeitstein.',
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
            "e19" => new EreignisCardDefinition(
                id: new CardId('e19'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Karriere-Boost',
                description: 'Dein großes Engagement in der Obdachlosenhilfe begeistert den Bürgermeister. Deshalb schlägt er dich für ein Stipendienprogramm vor. Du wirst ausgewählt und erhältst 12.000 € Unterstützung.',
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
            "e20" => new EreignisCardDefinition(
                id: new CardId('e20'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Work-Life-Balance',
                description: 'Du hast die optimale Mitte gefunden und eine neue Routine entwickelt. Es eröffnen sich zahlreiche neue Möglichkeiten.',
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
            "e21" => new EreignisCardDefinition(
                id: new CardId('e21'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stress',
                description: 'Du kannst mit dem Druck nicht umgehen und schläfst nicht genug. Dadurch bist du zunehmend unfreundlich zu deinem Kollegium. Das schadet deinem Netzwerk sehr. Es wieder aufzubauen, kostet dich einen Zeitstein.',
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
            "e22" => new EreignisCardDefinition(
                id: new CardId('e22'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Finanzierung private Universität',
                description: 'Du ermöglichst deinem Kind den Besuch einer privaten Universität und profitierst selbst: Deine Besuche vor Ort erweitern auch deinen eigenen Horizont.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-70000),
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
            "e23" => new EreignisCardDefinition(
                id: new CardId('e23'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du wirst befördert und dein Gehalt steigt in diesem Jahr um 30 %. Da sich deine Arbeitszeiten erhöhen, verlierst du einen Zeitstein.',
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
                    modifyGehaltPercent:130,
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e24" => new EreignisCardDefinition(
                id: new CardId('e24'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Karriere-Boost',
                description: 'Dein großes Engagement in einem Flüchtlingsheim begeistert den Bürgermeister. Deshalb schlägt er dich für ein Stipendienprogramm vor. Du wirst ausgewählt und erhältst 8.000 € Unterstützung.',
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
            "e25" => new EreignisCardDefinition(
                id: new CardId('e25'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Stress',
                description: 'Du kannst mit dem Druck nicht umgehen und schläfst nicht genug. Dadurch bist du zunehmend unfreundlich zu deinem Kollegium. Das schadet deinem Netzwerk sehr. Es wieder aufzubauen, kostet dich einen Zeitstein.',
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
            "e26" => new EreignisCardDefinition(
                id: new CardId('e26'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Netzwerktreffen',
                description: 'Auf einem Netzwerktreffen für junge Absolventinnen und Absolventen erweiterst du dein berufliches Netzwerk und lernst neue Menschen kennen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-20000),
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
            "e27" => new EreignisCardDefinition(
                id: new CardId('e27'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Talentscout',
                description: 'Schon länger hast du die Aufmerksamkeit eines Talentscouts auf dich gezogen. Nun bekommst du die einmalige Gelegenheit, dich in einem Profifußballclub zu beweisen.',
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
                requiredCardId: new CardId('suf25'),
                gewichtung: 4,
            ),
            "e28" => new EreignisCardDefinition(
                id: new CardId('e28'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Work-Life-Balance',
                description: 'Du hast die optimale Mitte gefunden und eine neue Routine entwickelt. Es eröffnen sich zahlreiche neue Möglichkeiten.',
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
            "e29" => new EreignisCardDefinition(
                id: new CardId('e29'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobkündigung',
                description: 'Du hast dich mit deinem Kollegium zerstritten. Aus Frust kündigst du unüberlegt deinen Job und erhältst bereits dieses Jahr kein Einkommen mehr. Aufgrund des Jobverlusts erhältst du einen zusätzlichen Zeitstein.',
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
            "e30" => new EreignisCardDefinition(
                id: new CardId('e30'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'Glückwunsch, deine Bewerbung um eine Auszeichnung für besondere Prüfungsleistungen war erfolgreich und du erhältst sogar nachträglich einen Teil deiner BAföG-Schulden zurück.',
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
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j2'),
                gewichtung: 4,
            ),
            "e31" => new EreignisCardDefinition(
                id: new CardId('e31'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Erkranktes Familienmitglied',
                description: 'In deiner Familie erkrankt eine dir nahestehende Person schwer. Du kümmerst dich um eine geeignete Behandlungsmethode, verpasst dadurch aber eine wichtige Fortbildung. Dies führt zum Verlust eines Zeitsteins.',
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
            "e32" => new EreignisCardDefinition(
                id: new CardId('e32'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beziehungskrise',
                description: 'Du entscheidest dich für eine Paartherapie, um deine Beziehung zu retten. Allerdings bleibt dir dadurch vorerst wenig Zeit für deine Bildung/Karriere.',
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
                requiredCardId: new CardId('e110'),
                gewichtung: 4,
            ),
            "e33" => new EreignisCardDefinition(
                id: new CardId('e33'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Abschalten smarte Endgeräte',
                description: 'Schalte deine smarten Endgeräte jeden Abend um 18 Uhr ab, um deine Konzentration zu erhöhen. Du verpasst dadurch aber wichtige Investitionschancen, weshalb du dieses Jahr keine Investitionen mehr (ver-)kaufen kannst.',
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
            "e34" => new EreignisCardDefinition(
                id: new CardId('e34'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Fehlendes Wissen',
                description: 'Mit den Jahren hast du viele Themen aus deiner Ausbildung vergessen, weil du sie nicht wiederholt hast. Das wird nun zum Problem und kostet dich einen Zeitstein.',
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
            "e35" => new EreignisCardDefinition(
                id: new CardId('e35'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Abschalten smarte Endgeräte',
                description: 'Schalte deine smarten Endgeräte jeden Abend um 18 Uhr ab, um deine Konzentration zu erhöhen. Du verpasst dadurch aber wichtige Investitionschancen, weshalb du dieses Jahr keine Investitionen mehr (ver-)kaufen kannst.',
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
            "e36" => new EreignisCardDefinition(
                id: new CardId('e36'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Auszeichnung',
                description: 'In Anerkennung deiner herausragenden Verdienste und deines langjährigen Engagements verleiht dir deine Heimatuniversität die Ehrendoktorwürde.',
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
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('j49'),
                gewichtung: 4,
            ),
            "e37" => new EreignisCardDefinition(
                id: new CardId('e37'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kinderbetreuung',
                description: 'Dein Kind hat Schwierigkeiten in der Schule und braucht mehr Aufmerksamkeit. Du verbringst deine Freizeit nun vermehrt mit deinem Nachwuchs und bildest dich weniger weiter.',
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
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e38" => new EreignisCardDefinition(
                id: new CardId('e38'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu zahlreichen Entlassungen. Auch du bist betroffen und wirst entlassen. Da du keinen Job mehr hast, erhältst du einen zusätzlichen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
            "e39" => new EreignisCardDefinition(
                id: new CardId('e39'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu zahlreichen Entlassungen. Auch du bist betroffen und wirst entlassen. Da du keinen Job mehr hast, erhältst du einen zusätzlichen Zeitstein.',
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
            "e40" => new EreignisCardDefinition(
                id: new CardId('e40'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Jobverlust',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu zahlreichen Entlassungen. Auch du bist betroffen und wirst entlassen. Da du keinen Job mehr hast, erhältst du einen zusätzlichen Zeitstein.',
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
            "e41" => new EreignisCardDefinition(
                id: new CardId('e41'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu Kurzarbeit. Du erhältst für dieses Jahr nur noch 50 % deines Gehalts.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e42" => new EreignisCardDefinition(
                id: new CardId('e42'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu Kurzarbeit. Du erhältst für dieses Jahr nur noch 50 % deines Gehalts.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e43" => new EreignisCardDefinition(
                id: new CardId('e43'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu Kurzarbeit. Du erhältst für dieses Jahr nur noch 50 % deines Gehalts.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e44" => new EreignisCardDefinition(
                id: new CardId('e44'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Eine chronische Sehnenscheidenentzündung zwingt dich, deinen Beruf aufzugeben. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e45" => new EreignisCardDefinition(
                id: new CardId('e45'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Ein Bandscheibenvorfall führt zu chronischen Rückenproblemen. Du kannst deinen Beruf nicht mehr ausüben. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e46" => new EreignisCardDefinition(
                id: new CardId('e46'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du leidest gelegentlich unter Schlafstörungen. Sie stören deinen Arbeitsalltag, hindern dich aber nicht an der Berufsausübung. Eine Berufsunfähigkeitsversicherung greift erst bei dauerhaften Einschränkungen. Du gibst deshalb in jedem Fall einen Zeitstein ab.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e47" => new EreignisCardDefinition(
                id: new CardId('e47'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Du leidest unter schweren Depressionen und kannst deinen Beruf über Monate nicht mehr ausüben. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e48" => new EreignisCardDefinition(
                id: new CardId('e48'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Ein Schlaganfall lähmt dich halbseitig. Trotz Reha bleibt die Lähmung, du kannst deinen Beruf nicht mehr ausüben. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e49" => new EreignisCardDefinition(
                id: new CardId('e49'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Bei einem Unfall erleidest du eine dauerhafte Querschnittslähmung und kannst deinen Beruf nicht mehr ausüben. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e50" => new EreignisCardDefinition(
                id: new CardId('e50'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Berufsunfähigkeitsversicherung',
                description: 'Beim Sport brichst du dir mehrere Wirbel. Die Folgen schränken dich so stark ein, dass du deinen Beruf aufgeben musst. Mit einer Berufsunfähigkeitsversicherung erhältst du trotz Jobverlust dein Gehalt weiter. Zum neuen Jahr kannst du einen neuen Job aufnehmen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e51" => new EreignisCardDefinition(
                id: new CardId('e51'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Trennung',
                description: 'Deine Partnerin beendet die Beziehung. Das macht sich auch bei der Arbeit bemerkbar, weshalb dir deine Führungskraft zu einer psychotherapeutischen Behandlung rät. Die liegengebliebene Arbeit musst du später aufholen, das kostet dich einen Zeitstein.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e52" => new EreignisCardDefinition(
                id: new CardId('e52'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu Kurzarbeit. Du erhältst für dieses Jahr nur noch 80 % deines Gehalts.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
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
            "e53" => new EreignisCardDefinition(
                id: new CardId('e53'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du möchtest dich privat weiterentwickeln und beginnst eine Weiterbildung am Abend. Damit du Zeit zum Lernen hast, arbeitest du in diesem Jahr nur noch 70 %. Dein Gehalt wird dementsprechend gekürzt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
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
            "e54" => new EreignisCardDefinition(
                id: new CardId('e54'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Kurzarbeit',
                description: 'Die wirtschaftliche Lage ist angespannt und es kommt zu Kurzarbeit. Du erhältst für dieses Jahr nur noch 60 % deines Gehalts.',
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
            "e55" => new EreignisCardDefinition(
                id: new CardId('e55'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Lohnerhöhung',
                description: 'Die Gehaltsverhandlungen mit deinen Vorgesetzten liefen sehr gut und du bekommst in diesem Jahr 30 % mehr Gehalt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
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
            "e56" => new EreignisCardDefinition(
                id: new CardId('e56'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Weihnachten',
                description: 'Weihnachten steht vor der Tür. Deine Teamleitung überrascht dich mit einem extra großen Weihnachtsgeld, da das Geschäftsjahr besonders gut ausfiel.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
                ],
                gewichtung: 1,
            ),
            "e57" => new EreignisCardDefinition(
                id: new CardId('e57'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du hast gemerkt, dass dich der Job mental stark fordert. Du willst besser auf dich achten und Stress vorbeugen. Deshalb arbeitest du dieses Jahr nur noch 80 %. Dein Gehalt wird entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
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
            "e58" => new EreignisCardDefinition(
                id: new CardId('e58'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du hast eine kreative Idee, die du endlich umsetzen willst. Um daran arbeiten zu können, reduzierst du deine Arbeitszeit in diesem Jahr auf 70 %. Dein Gehalt wird entsprechend gekürzt.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
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
            "e59" => new EreignisCardDefinition(
                id: new CardId('e59'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du planst eine berufliche Neuorientierung. Um dich darauf vorzubereiten, besuchst du eine Umschulung. Dafür brauchst du mehr Zeit. Du arbeitest deshalb nur noch 50 %. Dein Gehalt sinkt in diesem Jahr.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
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
            "e60" => new EreignisCardDefinition(
                id: new CardId('e60'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du machst deinen Job hervorragend und bekommst eine unerwartete Beförderung. Dein Gehalt steigt in diesem Jahr um 10 %.',
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
            "e61" => new EreignisCardDefinition(
                id: new CardId('e61'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Streit Kollegium',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust nimmst du dir zunächst vier Wochen unbezahlten Urlaub. Dies kostet dich 3.000 €.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-3000),
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
            "e62" => new EreignisCardDefinition(
                id: new CardId('e62'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Betriebliche Weihnachtsfeier',
                description: 'Bei einer betrieblichen Weihnachtsfeier hast du etwas zu tief ins Glas geschaut und streng geheime interne Beschlüsse weitererzählt. Das hat fatale Folgen und kostet dich deinen Job.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
            "e63" => new EreignisCardDefinition(
                id: new CardId('e63'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Betriebliche Weihnachtsfeier',
                description: 'Bei einer betrieblichen Weihnachtsfeier hast du etwas zu tief ins Glas geschaut und streng geheime interne Beschlüsse weitererzählt. Das hat fatale Folgen und kostet dich deinen Job.',
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
                description: 'Bei einer betrieblichen Weihnachtsfeier hast du etwas zu tief ins Glas geschaut und streng geheime interne Beschlüsse weitererzählt. Das hat fatale Folgen und kostet dich deinen Job.',
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
            "e65" => new EreignisCardDefinition(
                id: new CardId('e65'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Prämie',
                description: 'Deine Vorgesetzten sind stolz auf dich. Du hast dich in den letzten Monaten enorm weiterentwickelt. Aufgrund deiner starken Leistungen ist der Umsatz um 20 % gestiegen. Dies wird mit einer Prämie von 5.000 € belohnt.',
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
                ],
                gewichtung: 1,
            ),
            "e66" => new EreignisCardDefinition(
                id: new CardId('e66'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Prämie',
                description: 'Deine Vorgesetzten sind stolz auf dich. Du hast dich in den letzten Monaten enorm weiterentwickelt. Aufgrund deiner starken Leistungen ist der Umsatz um 20 % gestiegen. Dies wird mit einer Prämie von 10.000 € belohnt.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(10000),
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
            "e67" => new EreignisCardDefinition(
                id: new CardId('e67'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Prämie',
                description: 'Deine Vorgesetzten sind stolz auf dich. Du hast dich in den letzten Monaten enorm weiterentwickelt. Aufgrund deiner starken Leistungen ist der Umsatz um 20 % gestiegen. Dies wird mit einer Prämie von 3.000 € belohnt.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(3000),
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
            "e68" => new EreignisCardDefinition(
                id: new CardId('e68'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Streit Kollegium',
                description: 'Du hast dich mit deinem gesamten Kollegium zerstritten. Aus Frust nimmst du dir zunächst vier Wochen unbezahlten Urlaub. Dies kostet dich 10.000 €.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-10000),
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
            "e69" => new EreignisCardDefinition(
                id: new CardId('e69'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Beförderung',
                description: 'Du machst deinen Job hervorragend und bekommst eine unerwartete Beförderung. Dein Gehalt steigt in diesem Jahr um 20 %.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
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
            "e70" => new EreignisCardDefinition(
                id: new CardId('e70'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du bereitest dich auf eine Führungsposition vor und nimmst an einem internen Entwicklungsprogramm teil. Um dich darauf konzentrieren zu können, reduzierst du deine Arbeitszeit auf 70 %. Dein Gehalt wird für dieses Jahr entsprechend angepasst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    bildungKompetenzsteinChange: +1,
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
            "e71" => new EreignisCardDefinition(
                id: new CardId('e71'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du willst dich fachlich spezialisieren. Du machst eine zertifizierte Weiterbildung, die mehrere Monate dauert. Damit du den Kurs neben dem Job bewältigen kannst, gehst du auf 50 % Arbeitszeit für dieses Jahr. Auch dein Gehalt reduziert sich entsprechend.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
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
            "e72" => new EreignisCardDefinition(
                id: new CardId('e72'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Arbeitszeitverkürzung',
                description: 'Du möchtest dich selbstständig machen. Du arbeitest an deiner Geschäftsidee und brauchst dafür Zeit. Deshalb reduzierst du deine Stelle auf 60 %. Du nutzt die freie Zeit für den Aufbau deines Unternehmens – bei reduziertem Gehalt für dieses Jahr.',
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
            "e73" => new EreignisCardDefinition(
                id: new CardId('e73'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du verschüttest Saft auf dem Laptop einer Kollegin. Der Laptop ist nicht mehr zu retten. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
            "e74" => new EreignisCardDefinition(
                id: new CardId('e74'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Dir fällt beim Präsentieren ein Beamer vom Tisch. Das Gerät ist defekt und muss ersetzt werden. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
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
            "e75" => new EreignisCardDefinition(
                id: new CardId('e75'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du stößt versehentlich einen Monitor in einem Uni-Seminarraum um. Das Display ist gesprungen, der Monitor unbrauchbar. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
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
            "e76" => new EreignisCardDefinition(
                id: new CardId('e76'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du leihst dir ein Tablet für ein Gruppenprojekt – und lässt es fallen. Der Bildschirm ist beschädigt. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
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
            "e77" => new EreignisCardDefinition(
                id: new CardId('e77'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du schließt versehentlich deine Wasserflasche nicht richtig und der Rucksack deiner Arbeitskollegin mit Laptop wird nass. Der Laptop funktioniert nicht mehr. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
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
            "e78" => new EreignisCardDefinition(
                id: new CardId('e78'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Bei einem Kundentermin stößt du aus Versehen eine teure Kamera vom Tisch. Die Kamera funktioniert nicht mehr. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
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
            "e79" => new EreignisCardDefinition(
                id: new CardId('e79'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du verschüttest Wasser auf die Tastatur deines Kollegen. Die Tastatur funktioniert nicht mehr. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-80),
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
            "e80" => new EreignisCardDefinition(
                id: new CardId('e80'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du verlierst einen Schlüssel für ein Bürogebäude. Die gesamte Schließanlage muss ersetzt werden. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
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
            "e81" => new EreignisCardDefinition(
                id: new CardId('e81'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du beschädigst beim Aufbau einer Firmenveranstaltung teure Bühnentechnik. Ein Lichtsystem fällt um und wird unbrauchbar. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
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
            "e82" => new EreignisCardDefinition(
                id: new CardId('e82'),
                categoryId: CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
                title: 'Haftpflichtversicherung',
                description: 'Du lädst versehentlich Schadsoftware per USB-Stick auf einen Arbeitsrechner. Ein Teil des internen Netzwerks fällt aus, es entstehen Wiederherstellungskosten. Solltest du eine Haftpflichtversicherung abgeschlossen haben, wird der Schaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-5000),
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
            "e83" => new EreignisCardDefinition(
                id: new CardId('e83'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du hast gemerkt, dass dich der Job mental stark fordert. Du willst besser auf dich achten und Stress vorbeugen. Deshalb arbeitest du dieses Jahr nur noch 50 %. Dein Gehalt wird entsprechend angepasst.',
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
            "e84" => new EreignisCardDefinition(
                id: new CardId('e84'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Aufgrund deines Jobs hast du deine Freundschaften nicht gepflegt. Das musst du dringend ändern. Du arbeitest deshalb für dieses Jahr nur noch 80 %. Dein Gehalt wird entsprechend um 20 % gekürzt.',
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
            "e85" => new EreignisCardDefinition(
                id: new CardId('e85'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du möchtest deinen Alltag entschleunigen, wieder mehr kochen, draußen sein, Freunde treffen. Dafür brauchst du Zeit. Deshalb arbeitest du dieses Jahr nur 60 %. Dein Gehalt wird entsprechend angepasst.',
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
            "e86" => new EreignisCardDefinition(
                id: new CardId('e86'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du kümmerst dich gleichzeitig um deine Kinder und deine Eltern. Um allen gerecht zu werden, reduzierst du deine Arbeitszeit auf 80 %. Dein Gehalt wird entsprechend angepasst.',
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
            "e87" => new EreignisCardDefinition(
                id: new CardId('e87'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Ein Familienmitglied braucht mehr Unterstützung im Alltag. Du übernimmst einen Teil der Pflege und reduzierst deshalb deine Arbeitszeit auf 70 %. Dein Gehalt wird dementsprechend angepasst.',
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
            "e88" => new EreignisCardDefinition(
                id: new CardId('e88'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Deine Tochter Alisa wird geboren, herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Einkommens, mindestens 1.000 €, und einmalig 2.000 € für die Erstausstattung. Über Babyschwimmen und Krabbelgruppe lernst du viele andere Eltern kennen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(2),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
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
                gewichtung: 8,
            ),
            "e89" => new EreignisCardDefinition(
                id: new CardId('e89'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Job kündigen und Weltreise',
                description: 'Du entscheidest dich, deinen Job zu kündigen und auf Reisen zu gehen, um dich neu zu orientieren. Du verlierst damit aber auch dein Einkommen für dieses Jahr. Dafür erhältst du einen zusätzlichen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
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
            "e90" => new EreignisCardDefinition(
                id: new CardId('e90'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geschenk',
                description: 'Deine beste Freundin plant eine Überraschung für dich. Sie spendiert dir ein Wellness-Wochenende in Südtirol.',
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
            "e92" => new EreignisCardDefinition(
                id: new CardId('e92'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Gewinn E-Bike',
                description: 'Herzlichen Glückwunsch! Du hast bei einer Verlosung ein E-Bike gewonnen. Dadurch bist du schneller bei deinen Terminen und kannst deine Freizeit mehr genießen.',
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
            "e93" => new EreignisCardDefinition(
                id: new CardId('e93'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Soziales Engagement',
                description: 'Deine Führungskraft schätzt dein soziales Engagement sehr, unterstützt deine Projekte gerne und gibt dir direkt Sonderurlaub für das nächste Sommercamp.',
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
            "e94" => new EreignisCardDefinition(
                id: new CardId('e94'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Babysitterin',
                description: 'Du engagierst eine Babysitterin, um mehr Zeit für dich zu haben.',
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
            "e95" => new EreignisCardDefinition(
                id: new CardId('e95'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer heftigen Influenza und liegst komplett flach. Gib einen Zeitstein ab, um dich zu erholen.',
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
            "e96" => new EreignisCardDefinition(
                id: new CardId('e96'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbau Überstunden',
                description: 'Du warst in den letzten Jahren so fleißig, dass du sehr viele Überstunden gesammelt hast. Nun ist es an der Zeit, diese abzubauen. Du nimmst dir einen Monat frei und reist durch Asien.',
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
            "e97" => new EreignisCardDefinition(
                id: new CardId('e97'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du hast dich mit deinem Team zerstritten und einigst dich mit deiner Chefin auf einen Aufhebungsvertrag. Damit verlierst du deinen aktuellen Job und bist erst einmal arbeitslos.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
            "e98" => new EreignisCardDefinition(
                id: new CardId('e98'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Burnout',
                description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Um dich wieder zu erholen, gehst du in eine Rehaklinik. Das kostet dich einen Zeitstein.',
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
                ],
                gewichtung: 1,
            ),
            "e99" => new EreignisCardDefinition(
                id: new CardId('e99'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Keine Börsenberichte mehr lesen',
                description: 'Du hast mehr Freizeit, weil du nicht mehr den Börsenbericht liest und die Zeitung mit dem hervorragenden Wirtschaftsressort abbestellt hast. Da du nicht mehr informiert bist, kannst du dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e100" => new EreignisCardDefinition(
                id: new CardId('e100'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Da Familie und Freundschaft für dich das Wichtigste im Leben sind, interessierst du dich kaum für Investitionen. Du darfst daher dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e101" => new EreignisCardDefinition(
                id: new CardId('e101'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Pause Investitionen',
                description: 'Du willst dich nicht mehr vom Auf und Ab auf den Finanzmärkten stressen lassen und machst eine Pause bei deinen Investitionen. Daher kannst du dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e102" => new EreignisCardDefinition(
                id: new CardId('e102'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Nicht beantragter Urlaub',
                description: 'Deine Urlaubstage sind aufgebraucht, also meldest du dich krank und fliegst nach Mallorca. Deine Freunde posten Fotos davon. Nach dem Urlaub liegt deine Kündigung auf dem Tisch. Du verlierst deinen Job und dein Einkommen für dieses Jahr.',
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
            "e103" => new EreignisCardDefinition(
                id: new CardId('e103'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'KiTa',
                description: 'Du findest für dein Kind einen Platz in einer KiTa mit kompetentem Betreuungspersonal.',
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
            "e104" => new EreignisCardDefinition(
                id: new CardId('e104'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Streit mit der Familie',
                description: 'An Weihnachten hast du dich mit deiner Schwester zerstritten. Du musst nun die Wogen wieder glätten. Das kostet dich einen Zeitstein.',
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
            "e105" => new EreignisCardDefinition(
                id: new CardId('e105'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Einsatz für heimische Bienen',
                description: 'Dein Einsatz für den Schutz heimischer Bienenpopulationen wird mit dem Deutschen Ehrenamtspreis ausgezeichnet.',
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
            "e106" => new EreignisCardDefinition(
                id: new CardId('e106'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsposten verlängern',
                description: 'Leider lässt sich keine andere Person finden, die deinen Vorstandsposten im Tennisverein übernimmt. Daher entscheidest du dich, den Posten für eine weitere Amtszeit zu übernehmen. Dies kostet dich einen Zeitstein.',
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
                requiredCardId: new CardId('suf31'),
                gewichtung: 4,
            ),
            "e107" => new EreignisCardDefinition(
                id: new CardId('e107'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sprachtandem Erasmus',
                description: 'Mit deinem Sprachtandem aus dem Erasmus-Programm verstehst du dich so gut, dass du sein Heimatland bereist. Bei der Reise lernst du viele tolle Menschen kennen.',
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
                requiredCardId: new CardId('suf10'),
                gewichtung: 4,
            ),
            "e108" => new EreignisCardDefinition(
                id: new CardId('e108'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Zweite Auflage der Flyer',
                description: 'Deine Informationsflyer über demokratische Werte kommen so gut an, dass du erneut Flyer in den Druck gibst. Diese kosten dich nochmals 500 €.',
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
                requiredCardId: new CardId('suf32'),
                gewichtung: 4,
            ),
            "e109" => new EreignisCardDefinition(
                id: new CardId('e109'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deines Nachbarn stören dich sehr und es kommt zu einem Rechtsstreit. Dabei entstehen Gerichtskosten von 800 €.',
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
            "e110" => new EreignisCardDefinition(
                id: new CardId('e110'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Hochzeit',
                description: 'Herzlichen Glückwunsch, du findest deine Partnerin fürs Leben. Der schönste Tag eures Lebens kostet dich 15.000 €. Ihr feiert im Kreis eurer Familien und Freunde.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
                    freizeitKompetenzsteinChange: +1,
                ),
                modifierIds: [
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                ],
                gewichtung: 10,
            ),
            "e111" => new EreignisCardDefinition(
                id: new CardId('e111'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitslosigkeit',
                description: 'Wegen unentschuldigten Fernbleibens von der Arbeit erhältst du eine fristlose Kündigung und verlierst dein Einkommen. Du hast nun wegen deiner Arbeitslosigkeit mehr Zeit. Daher erhältst du einen Zeitstein.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
            "e112" => new EreignisCardDefinition(
                id: new CardId('e112'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Preis für Integration',
                description: 'Herzlichen Glückwunsch! Du gewinnst den Preis für Integration. Dein Projekt „Sportintegration“ bringt Jugendliche mit und ohne Beeinträchtigung zusammen, indem sie gemeinsam Sport treiben.',
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
            "e113" => new EreignisCardDefinition(
                id: new CardId('e113'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Tristan wird geboren, herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Einkommens, mindestens 1.000 €, und einmalig 2.000 € für die Erstausstattung. Über Babyschwimmen und Krabbelgruppe lernst du viele andere Eltern kennen.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
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
                gewichtung: 4,
            ),
            "e114" => new EreignisCardDefinition(
                id: new CardId('e114'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Liam wird geboren, herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Einkommens, mindestens 1.000 €, und einmalig 2.000 € für die Erstausstattung. Über Babyschwimmen und Krabbelgruppe lernst du viele andere Eltern kennen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
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
                gewichtung: 4,
            ),
            "e115" => new EreignisCardDefinition(
                id: new CardId('e115'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Deine Tochter Elif wird geboren, herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Einkommens, mindestens 1.000 €, und einmalig 2.000 € für die Erstausstattung. Über Babyschwimmen und Krabbelgruppe lernst du viele andere Eltern kennen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
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
                gewichtung: 4,
            ),
            "e116" => new EreignisCardDefinition(
                id: new CardId('e116'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Dein Sohn Ali wird geboren, herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Einkommens, mindestens 1.000 €, und einmalig 2.000 € für die Erstausstattung. Über Babyschwimmen und Krabbelgruppe lernst du viele andere Eltern kennen.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
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
                gewichtung: 4,
            ),
            "e117" => new EreignisCardDefinition(
                id: new CardId('e117'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburt',
                description: 'Deine Tochter Sophie wird geboren, herzlichen Glückwunsch! Ab jetzt zahlst du regelmäßig 10 % deines Einkommens, mindestens 1.000 €, und einmalig 2.000 € für die Erstausstattung. Über Babyschwimmen und Krabbelgruppe lernst du viele andere Eltern kennen.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-2000),
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
                gewichtung: 4,
            ),
            "e118" => new EreignisCardDefinition(
                id: new CardId('e118'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Engagement Obdachlosenheim',
                description: 'Um am Aufbau eines Obdachlosenheims mitzuwirken, reduzierst du dieses Jahr deine Arbeitszeit um 10 %. Dein Gehalt reduziert sich dementsprechend.',
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
            "e119" => new EreignisCardDefinition(
                id: new CardId('e119'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Training Jugendmannschaft',
                description: 'Du entscheidest dich dazu, die Jugendmannschaft deines Fußballvereins zu trainieren. Dafür kannst du dich nicht mehr auf deine Investitionen konzentrieren. Du darfst in diesem Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e120" => new EreignisCardDefinition(
                id: new CardId('e120'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Verhalten Kind',
                description: 'Dein Kind darf wegen aggressiven Verhaltens gegenüber anderen Kindern den Kindergarten vorläufig nicht mehr besuchen. Daher betreust du es selbst und reduzierst deine Arbeitszeit in diesem Jahr auf 75 %. Dein Gehalt sinkt entsprechend.',
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
            "e121" => new EreignisCardDefinition(
                id: new CardId('e121'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Handysucht',
                description: 'Bei jedem Treffen mit Freundinnen und Freunden hängst du immer nur am Handy. Das nervt deine Freunde und du wirst zunehmend ausgegrenzt. Die Freundschaften wieder aufzubauen, kostet Zeit.',
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
            "e122" => new EreignisCardDefinition(
                id: new CardId('e122'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Trennung',
                description: 'Dein Partner beendet die Beziehung mit dir. Du fällst in ein tiefes Loch.',
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
            "e123" => new EreignisCardDefinition(
                id: new CardId('e123'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie',
                description: 'Dein Kind möchte unbedingt auf ein privates Sportinternat. Es kommen Kosten von 30.000 € auf dich zu. Dafür lernst du viele andere Eltern kennen.',
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
            "e124" => new EreignisCardDefinition(
                id: new CardId('e124'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Lottogewinn',
                description: 'Glückwunsch! Du hast im Lotto gewonnen! Die Gewinnsumme beträgt 40.000 €.',
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
            "e125" => new EreignisCardDefinition(
                id: new CardId('e125'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Hochzeit',
                description: 'Herzlichen Glückwunsch, du findest deine Partnerin fürs Leben. Der schönste Tag eures Lebens kostet dich 25.000 €. Ihr feiert im Kreis eurer Familien und Freunde.',
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
                ],
                gewichtung: 10,
            ),
            "e126" => new EreignisCardDefinition(
                id: new CardId('e126'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Hochzeit',
                description: 'Herzlichen Glückwunsch, du findest deine Partnerin fürs Leben. Der schönste Tag eures Lebens kostet dich 35.000 €. Ihr feiert im Kreis eurer Familien und Freunde.',
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
                ],
                gewichtung: 10,
            ),
            "e127" => new EreignisCardDefinition(
                id: new CardId('e127'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Da du dich für sozial benachteiligte Menschen mit Beeinträchtigungen einsetzt und ihnen hilfst, an der Gesellschaft teilzunehmen, bekommst du eine Auszeichnung. Der Preis ist mit 50.000 € dotiert. Herzlichen Glückwunsch!',
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
            "e128" => new EreignisCardDefinition(
                id: new CardId('e128'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie',
                description: 'Dein Kind gewinnt einen renommierten Musikwettbewerb. Du bist so stolz, dass du eine neue Violine für 10.000 € kaufst und der Musiklehrerin einen Bonus von 5.000 € zahlst.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
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
            "e129" => new EreignisCardDefinition(
                id: new CardId('e129'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Freundschaft pflegen',
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
            "e130" => new EreignisCardDefinition(
                id: new CardId('e130'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Training Jugendmannschaft',
                description: 'Du entscheidest dich dazu, die Jugendmannschaft deines Fußballvereins zu trainieren. Dafür kannst du dich nicht mehr auf deine Investitionen konzentrieren. Du darfst in diesem Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e131" => new EreignisCardDefinition(
                id: new CardId('e131'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Verhalten Kind',
                description: 'Dein Kind darf wegen aggressiven Verhaltens gegenüber anderen Kindern den Kindergarten vorläufig nicht mehr besuchen. Daher betreust du es selbst und reduzierst deine Arbeitszeit in diesem Jahr auf 75 %. Dein Gehalt sinkt entsprechend.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e132" => new EreignisCardDefinition(
                id: new CardId('e132'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Instagramsucht',
                description: 'Bei jedem Treffen mit Freunden fotografierst du und postest sofort auf Instagram. Das stört sie, weil dir die Darstellung wichtiger scheint als der Moment. Das ständige Posten kostet dich außerdem viel Zeit.',
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
            "e133" => new EreignisCardDefinition(
                id: new CardId('e133'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Trennung',
                description: 'Dein Partner beendet die Beziehung mit dir. Du fällst in ein tiefes Loch.',
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
            "e134" => new EreignisCardDefinition(
                id: new CardId('e134'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie',
                description: 'Dein Kind möchte unbedingt auf ein privates Sportinternat. Es kommen Kosten von 50.000 € auf dich zu. Dafür lernst du viele andere Eltern kennen.',
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
            "e135" => new EreignisCardDefinition(
                id: new CardId('e135'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Gewinn beim Roulette',
                description: 'Glückwunsch! Du hast den Jackpot beim Roulette gewonnen! Die Gewinnsumme beträgt 60.000 €.',
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
            "e136" => new EreignisCardDefinition(
                id: new CardId('e136'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie',
                description: 'Dein Kind gewinnt einen renommierten Musikwettbewerb. Du bist so stolz, dass du eine neue Violine für 10.000 € kaufst und der Musiklehrerin einen Bonus von 5.000 € zahlst.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-15000),
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
                title: 'Pause Investitionen',
                description: 'Du willst dich nicht mehr vom Auf und Ab auf den Finanzmärkten stressen lassen und machst eine Pause bei deinen Investitionen. Daher kannst du dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e138" => new EreignisCardDefinition(
                id: new CardId('e138'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbestellung Börsenbericht',
                description: 'Du hast mehr Freizeit, weil du nicht mehr den Börsenbericht liest und die Zeitung mit dem hervorragenden Wirtschaftsressort abbestellt hast. Da du nicht mehr informiert bist, kannst du dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e139" => new EreignisCardDefinition(
                id: new CardId('e139'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Nachhilfe',
                description: 'Deine Kinder erhalten privaten Nachhilfeunterricht, um sich in der Schule zu verbessern.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(-28000),
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
            "e140" => new EreignisCardDefinition(
                id: new CardId('e140'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sabbatjahr',
                description: 'Du nimmst dir ein Sabbatjahr und reist um die Welt. Daher bekommst du dieses Jahr kein Gehalt.',
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
            "e141" => new EreignisCardDefinition(
                id: new CardId('e141'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Da Familie und Freundschaft für dich das Wichtigste im Leben sind, interessierst du dich kaum für Investitionen. Du darfst daher dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e142" => new EreignisCardDefinition(
                id: new CardId('e142'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Bauernhof-KiTa',
                description: 'Die Bauernhof-KiTa hat wieder Plätze frei. Du gibst dein Kind dort in die Obhut von kompetentem Betreuungspersonal in einer natürlichen Umgebung.',
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
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e143" => new EreignisCardDefinition(
                id: new CardId('e143'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Auszeichnung',
                description: 'Da du dich für sozial benachteiligte Menschen mit Beeinträchtigungen einsetzt und ihnen hilfst, an der Gesellschaft teilzunehmen, bekommst du eine Auszeichnung. Der Preis ist mit 60.000 € dotiert. Herzlichen Glückwunsch!',
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
            "e144" => new EreignisCardDefinition(
                id: new CardId('e144'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geschenk',
                description: 'Deine beste Freundin plant eine Überraschung für dich. Sie spendiert dir ein Wellness-Wochenende in Südtirol.',
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
            "e145" => new EreignisCardDefinition(
                id: new CardId('e145'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Burnout',
                description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Um dich wieder zu erholen, gehst du in eine Rehaklinik. Das kostet dich einen Zeitstein.',
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
            "e146" => new EreignisCardDefinition(
                id: new CardId('e146'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburtstagsparty',
                description: 'Du planst eine Geburtstagsparty für alle deine Freunde und Familienmitglieder. Doch so eine Party zu organisieren, kostet viel Zeit. Du verlierst einen Zeitstein.',
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
            "e147" => new EreignisCardDefinition(
                id: new CardId('e147'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Streit mit der Familie',
                description: 'An Weihnachten hast du dich mit deiner Schwester zerstritten. Du musst nun die Wogen wieder glätten. Das kostet dich einen Zeitstein.',
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
            "e149" => new EreignisCardDefinition(
                id: new CardId('e149'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Gewinn E-Roller',
                description: 'Herzlichen Glückwunsch! Du hast bei einer Verlosung einen E-Roller gewonnen. Dadurch bist du schneller bei Terminen und kannst deine Freizeit ausgiebig genießen.',
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
            "e150" => new EreignisCardDefinition(
                id: new CardId('e150'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Ein neues Tier zieht bei dir ein und braucht viel Aufmerksamkeit – besonders in der Anfangszeit. Du reduzierst deine Arbeitszeit auf 80 %, um dich gut kümmern zu können. Dein Gehalt verringert sich entsprechend.',
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
            "e151" => new EreignisCardDefinition(
                id: new CardId('e151'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du kümmerst dich gleichzeitig um deine Kinder und deine Eltern. Um allen gerecht zu werden, reduzierst du deine Arbeitszeit auf 70 %. Dein Gehalt wird entsprechend angepasst.',
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
            "e152" => new EreignisCardDefinition(
                id: new CardId('e152'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Ein Familienmitglied braucht mehr Unterstützung im Alltag. Du übernimmst einen Teil der Pflege und reduzierst deshalb deine Arbeitszeit auf 60 %. Dein Gehalt wird dementsprechend angepasst.',
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
            "e153" => new EreignisCardDefinition(
                id: new CardId('e153'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du erfüllst dir einen lang gehegten Wunsch: eine mehrmonatige Reise durch Südamerika. Um die Reise vorzubereiten und später flexibel zu sein, reduzierst du deine Arbeitszeit dieses Jahr auf 80 %. Dein Gehalt passt sich entsprechend an.',
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
            "e154" => new EreignisCardDefinition(
                id: new CardId('e154'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Du malst, schreibst oder machst Musik – und möchtest dafür mehr Raum haben. Deshalb arbeitest du in diesem Jahr nur noch 70 %, um dein kreatives Projekt weiterzuentwickeln. Dein Gehalt wird entsprechend reduziert.',
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
            "e155" => new EreignisCardDefinition(
                id: new CardId('e155'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitszeitverkürzung',
                description: 'Ein Familienmitglied braucht mehr Unterstützung im Alltag. Du übernimmst einen Teil der Pflege und reduzierst deshalb deine Arbeitszeit auf 60 %. Dein Gehalt wird dementsprechend angepasst.',
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
            "e156" => new EreignisCardDefinition(
                id: new CardId('e156'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbestellung Börsenbericht',
                description: 'Du hast mehr Freizeit, weil du nicht mehr den Börsenbericht liest und die Zeitung mit dem hervorragenden Wirtschaftsressort abbestellt hast. Da du nicht mehr informiert bist, kannst du dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e157" => new EreignisCardDefinition(
                id: new CardId('e157'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Pause Investitionen',
                description: 'Du willst dich nicht mehr vom Auf und Ab auf den Finanzmärkten stressen lassen und machst eine Pause bei deinen Investitionen. Daher kannst du dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e158" => new EreignisCardDefinition(
                id: new CardId('e158'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Assistenz im Berufsalltag',
                description: 'Du stellst eine persönliche Assistentin für deine beruflichen Aufgaben ein. Das kostet dich 30 % deines Gehalts.',
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
            "e159" => new EreignisCardDefinition(
                id: new CardId('e159'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Sabbatjahr',
                description: 'Du nimmst dir ein Sabbatjahr und reist um die Welt. Daher bekommst du dieses Jahr kein Gehalt.',
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
            "e160" => new EreignisCardDefinition(
                id: new CardId('e160'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Ferienlager in Übersee',
                description: 'Du meldest dein Kind für ein Ferienlager in Übersee an. Die Reisekosten sind zwar hoch, du hast aber auch einmal Zeit für dich.',
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
            "e161" => new EreignisCardDefinition(
                id: new CardId('e161'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Familie und Freundschaft',
                description: 'Da Familie und Freundschaft für dich das Wichtigste im Leben sind, interessierst du dich kaum für Investitionen. Du darfst daher dieses Jahr keine Investitionen mehr (ver-)kaufen.',
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
            "e162" => new EreignisCardDefinition(
                id: new CardId('e162'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer Herzmuskelentzündung und liegst komplett flach. Nutze einen Zeitstein, um dich zu erholen.',
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
            "e164" => new EreignisCardDefinition(
                id: new CardId('e164'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geschenk',
                description: 'Dein Partner plant eine Überraschung für dich. Er nimmt dich mit auf eine Kreuzfahrt durch die Karibik.',
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
            "e165" => new EreignisCardDefinition(
                id: new CardId('e165'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Burnout',
                description: 'Bei der Verfolgung deines Traums hast du die Pausen ganz vergessen. Um dich wieder zu erholen, gehst du in eine Rehaklinik. Das kostet dich einen Zeitstein.',
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
            "e166" => new EreignisCardDefinition(
                id: new CardId('e166'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Streit mit der Familie',
                description: 'An Weihnachten hast du dich mit deiner Schwester zerstritten. Du musst nun die Wogen wieder glätten. Das kostet dich einen Zeitstein.',
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
            "e167" => new EreignisCardDefinition(
                id: new CardId('e167'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du musst dich einer dringenden Operation unterziehen und liegst komplett flach. Nutze einen Zeitstein, um dich zu erholen.',
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
            "e168" => new EreignisCardDefinition(
                id: new CardId('e168'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Chauffeurdienst',
                description: 'Herzlichen Glückwunsch! Du hast bei einer Verlosung einen einjährigen Chauffeurdienst gewonnen. Dadurch bist du schneller bei Terminen und kannst deine Freizeit besser genießen.',
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
            "e169" => new EreignisCardDefinition(
                id: new CardId('e169'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Freundschaft pflegen',
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
            "e170" => new EreignisCardDefinition(
                id: new CardId('e170'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Geburtstagsparty',
                description: 'Du planst eine Geburtstagsparty und lädst alle deine Freunde und Familienmitglieder ein. Doch so eine Party zu organisieren, kostet viel Zeit. Du verlierst einen Zeitstein.',
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
            "e171" => new EreignisCardDefinition(
                id: new CardId('e171'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Job kündigen und Weltreise',
                description: 'Du entscheidest dich, deinen Job zu kündigen und auf Reisen zu gehen, um dich neu zu orientieren. Du verlierst damit aber auch dein Einkommen. Aufgrund des Jobverlusts erhältst du einen zusätzlichen Zeitstein.',
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
            "e172" => new EreignisCardDefinition(
                id: new CardId('e172'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Job kündigen und Weltreise',
                description: 'Du entscheidest dich, deinen Job zu kündigen und auf Reisen zu gehen, um dich neu zu orientieren. Du verlierst damit aber auch dein Einkommen. Aufgrund des Jobverlusts erhältst du einen zusätzlichen Zeitstein.',
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
            "e173" => new EreignisCardDefinition(
                id: new CardId('e173'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbau Überstunden',
                description: 'Du warst in den letzten Jahren so fleißig, dass du sehr viele Überstunden gesammelt hast. Nun ist es an der Zeit, diese abzubauen. Du nimmst dir einen Monat frei und reist durch Asien.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e174" => new EreignisCardDefinition(
                id: new CardId('e174'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Abbau Überstunden',
                description: 'Du warst in den letzten Jahren so fleißig, dass du sehr viele Überstunden gesammelt hast. Nun ist es an der Zeit, diese abzubauen. Du nimmst dir einen Monat frei und reist durch Asien.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e175" => new EreignisCardDefinition(
                id: new CardId('e175'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitslosigkeit',
                description: 'Wegen unentschuldigten Fernbleibens von der Arbeit erhältst du eine fristlose Kündigung und verlierst dein Einkommen. Du hast nun wegen deiner Arbeitslosigkeit mehr Zeit. Daher erhältst du einen Zeitstein.',
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
            "e176" => new EreignisCardDefinition(
                id: new CardId('e176'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Arbeitslosigkeit',
                description: 'Wegen unentschuldigten Fernbleibens von der Arbeit erhältst du eine fristlose Kündigung und verlierst dein Einkommen. Du hast nun wegen deiner Arbeitslosigkeit mehr Zeit. Daher erhältst du einen Zeitstein.',
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
            "e177" => new EreignisCardDefinition(
                id: new CardId('e177'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Zweite Auflage der Flyer',
                description: 'Deine Informationsflyer über demokratische Werte kommen so gut an, dass du erneut Flyer in den Druck gibst. Das kostet dich nochmals 1.000 €.',
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
                requiredCardId: new CardId('suf32'),
                gewichtung: 4,
            ),
            "e178" => new EreignisCardDefinition(
                id: new CardId('e178'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Zweite Auflage der Flyer',
                description: 'Deine Informationsflyer über demokratische Werte kommen so gut an, dass du erneut Flyer in den Druck gibst. Das kostet dich nochmals 2.000 €.',
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
                requiredCardId: new CardId('suf32'),
                gewichtung: 4,
            ),
            "e179" => new EreignisCardDefinition(
                id: new CardId('e179'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du hast dich mit deinem Team zerstritten und einigst dich mit deiner Chefin auf einen Aufhebungsvertrag. Damit verlierst du deinen aktuellen Job und bist erst einmal arbeitslos.',
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
            "e180" => new EreignisCardDefinition(
                id: new CardId('e180'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Jobverlust',
                description: 'Du hast dich mit deinem Team zerstritten und einigst dich mit deiner Chefin auf einen Aufhebungsvertrag. Damit verlierst du deinen aktuellen Job und bist erst einmal arbeitslos.',
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
            "e181" => new EreignisCardDefinition(
                id: new CardId('e181'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Du erkrankst an einer Herzmuskelentzündung und liegst komplett flach. Nutze einen Zeitstein, um dich zu erholen.',
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
            "e182" => new EreignisCardDefinition(
                id: new CardId('e182'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Dein Kind hat Windpocken. Nutze einen Zeitstein, um dich zu erholen.',
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
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e183" => new EreignisCardDefinition(
                id: new CardId('e183'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Krankheit',
                description: 'Dein Kind hat Läuse. Nutze einen Zeitstein, um dich zu erholen.',
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
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e184" => new EreignisCardDefinition(
                id: new CardId('e184'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Lottogewinn',
                description: 'Glückwunsch! Du hast den Jackpot gewonnen! Die Gewinnsumme beträgt 10.000 €.',
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
            "e185" => new EreignisCardDefinition(
                id: new CardId('e185'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Preis für soziales Engagement',
                description: 'Herzlichen Glückwunsch! Du gewinnst den Preis für soziales Engagement. Mit deinem Einsatz im Flüchtlingszentrum hast du vielen Menschen geholfen, sich schnell zu integrieren.',
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
            "e186" => new EreignisCardDefinition(
                id: new CardId('e186'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Preis für Umweltprojekt',
                description: 'Herzlichen Glückwunsch! Du gewinnst den Preis für Umweltengagement. Mit deinem Projekt „Bienen in Schulen“ hast du viele Bienenhotels gebaut und Kindern die Bedeutung der Bienen nähergebracht.',
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
            "e187" => new EreignisCardDefinition(
                id: new CardId('e187'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deines Nachbarn stören dich sehr und es kommt zu einem Rechtsstreit. Dabei entstehen Gerichtskosten von 2.000 €.',
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
            "e188" => new EreignisCardDefinition(
                id: new CardId('e188'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Rechtsstreit',
                description: 'Die lauten Partys deines Nachbarn stören dich sehr und es kommt zu einem Rechtsstreit. Dabei entstehen Gerichtskosten von 5.000 €.',
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
            "e189" => new EreignisCardDefinition(
                id: new CardId('e189'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Soziales Engagement',
                description: 'Deine Führungskraft schätzt dein soziales Engagement sehr, unterstützt deine Projekte gerne und gibt dir direkt Sonderurlaub für das nächste Sommercamp.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e190" => new EreignisCardDefinition(
                id: new CardId('e190'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Soziales Engagement',
                description: 'Deine Führungskraft schätzt dein soziales Engagement sehr, unterstützt deine Projekte gerne und gibt dir direkt Sonderurlaub für das nächste Sommercamp.',
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
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e191" => new EreignisCardDefinition(
                id: new CardId('e191'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsposten verlängern',
                description: 'Leider lässt sich keine andere Person finden, die deinen Vorstandsposten im Tennisverein übernimmt. Daher entscheidest du dich, den Posten für eine weitere Amtszeit zu übernehmen. Dies kostet dich einen Zeitstein.',
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
                requiredCardId: new CardId('suf143'),
                gewichtung: 4,
            ),
            "e192" => new EreignisCardDefinition(
                id: new CardId('e192'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Vorstandsposten verlängern',
                description: 'Leider lässt sich keine andere Person finden, die deinen Vorstandsposten im Tennisverein übernimmt. Daher entscheidest du dich, den Posten für eine weitere Amtszeit zu übernehmen. Dies kostet dich einen Zeitstein.',
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
                requiredCardId: new CardId('suf144'),
                gewichtung: 4,
            ),
            "e193" => new EreignisCardDefinition(
                id: new CardId('e193'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du hilfst beim Umzug und lässt einen Fernseher fallen. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten übernommen.',
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
            "e194" => new EreignisCardDefinition(
                id: new CardId('e194'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du wirfst beim Aufräumen versehentlich deinen eigenen Laptop vom Tisch. Auch mit Haftpflichtversicherung musst du die Kosten selbst tragen, denn die Versicherung zahlt nur für Schäden an fremdem Eigentum.',
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
            "e195" => new EreignisCardDefinition(
                id: new CardId('e195'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du trittst beim Fußballspielen aus Versehen einem anderen Spieler gegen das Knie. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für Personenschäden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_1,
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
                    EreignisPrerequisitesId::HAS_SPECIFIC_CARD,
                ],
                requiredCardId: new CardId('suf25'),
                gewichtung: 4,
            ),
            "e196" => new EreignisCardDefinition(
                id: new CardId('e196'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du stößt beim Einkaufen aus Versehen an eine teure Vase, welche umfällt und zu Bruch geht. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
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
            "e197" => new EreignisCardDefinition(
                id: new CardId('e197'),
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
            "e198" => new EreignisCardDefinition(
                id: new CardId('e198'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Dein Kleinkind zerkratzt ein parkendes Auto mit einem Stein. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
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
                    EreignisPrerequisitesId::HAS_CHILD,
                ],
                gewichtung: 4,
            ),
            "e199" => new EreignisCardDefinition(
                id: new CardId('e199'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Grillen auf dem Balkon deines Mietshauses entsteht ein Rußschaden an der Fassade. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
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
            "e200" => new EreignisCardDefinition(
                id: new CardId('e200'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du hast deine Waschmaschine falsch angeschlossen und das Parkett in der Mietwohnung damit ruiniert. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
                phaseId: LebenszielPhaseId::PHASE_2,
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
            "e201" => new EreignisCardDefinition(
                id: new CardId('e201'),
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
            "e202" => new EreignisCardDefinition(
                id: new CardId('e202'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du lässt ein Handtuch über einer Stehlampe hängen – es fängt Feuer und beschädigt Teile des Hotelzimmers. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
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
            "e203" => new EreignisCardDefinition(
                id: new CardId('e203'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du kollidierst beim Volleyball mit einem Mitspieler. Seine Zahnprothese geht kaputt. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Ersatz übernommen.',
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
                requiredCardId: new CardId('suf21'),
                gewichtung: 4,
            ),
            "e204" => new EreignisCardDefinition(
                id: new CardId('e204'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Du schließt einen Geschirrspüler in der Mietwohnung selbst an – ein Schlauch platzt. Das Wasser läuft bis in die Wohnung darunter. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten für den Sachschaden übernommen.',
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
            "e205" => new EreignisCardDefinition(
                id: new CardId('e205'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Heimwerken rutschst du ab und brichst dir die Hand. Die Verletzung schränkt dich dauerhaft ein. Du musst deinen Job abgeben. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du einmalig eine Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(6000),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
                    ModifierId::JOBVERLUST,
                ],
                modifierParameters: new ModifierParameters(
                ),
                ereignisRequirementIds: [
                    EreignisPrerequisitesId::HAS_JOB,
                ],
                gewichtung: 1,
            ),
            "e206" => new EreignisCardDefinition(
                id: new CardId('e206'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Du stürzt beim Eislaufen und prellst dir das Handgelenk. Du kannst dadurch langfristig nicht mehr deiner Bürotätigkeit nachgehen. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du einmalig eine Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_1,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(2000),
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
            "e207" => new EreignisCardDefinition(
                id: new CardId('e207'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Sportklettern stürzt du und brichst dir den Arm mehrfach. Du kannst deinen Job nicht länger ausüben und erhältst vorerst nur 70 % deines diesjährigen Gehalts. Mit einer privaten Unfallversicherung erhältst du einmalig eine Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(8500),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
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
            "e208" => new EreignisCardDefinition(
                id: new CardId('e208'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Du stürzt beim Inlineskaten, hast einen komplizierten Bruch und musst bei der Arbeit lange aussetzen. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du einmalig eine Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_2,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(9000),
                    zeitsteineChange: -1,
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
            "e209" => new EreignisCardDefinition(
                id: new CardId('e209'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Reinigen der Dachrinne stürzt du auf den Rücken. Die Diagnose lautet: Bruch mehrerer Wirbel und langer Arbeitsausfall. Im Falle einer abgeschlossenen privaten Unfallversicherung erhältst du einmalig eine Invaliditätsleistung.',
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
            "e210" => new EreignisCardDefinition(
                id: new CardId('e210'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Private Unfallversicherung',
                description: 'Beim Streichen der Wände stürzt du von der Leiter und brichst dir die Schulter. Du kannst deinen Job nicht länger ausüben und erhältst vorerst 70 % deines diesjährigen Gehalts. Mit einer privaten Unfallversicherung erhältst du einmalig eine Invaliditätsleistung.',
                phaseId: LebenszielPhaseId::PHASE_3,
                year: new Year(3),
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(5500),
                ),
                modifierIds: [
                    ModifierId::PRIVATE_UNFALLVERSICHERUNG,
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
            "e213" => new EreignisCardDefinition(
                id: new CardId('e213'),
                categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
                title: 'Haftpflichtversicherung',
                description: 'Beim Inlineskaten kollidierst du unabsichtlich mit einer Radfahrerin, die verletzt wird. Im Falle einer abgeschlossenen Haftpflichtversicherung werden die Kosten übernommen.',
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
            "wb1" => new WeiterbildungCardDefinition(
                id: new CardId('wb1'),
                description: 'Welche handelspolitische Maßnahme dient dem Schutz der heimischen Wirtschaft vor ausländischer Konkurrenz?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Einführung von Zöllen auf ausländische Waren', true),
                    new AnswerOption(new AnswerId("c"), 'Abschaffung von Zöllen'),
                    new AnswerOption(new AnswerId("b"), 'Freier Zugang für alle ausländischen Anbieter'),
                ],
            ),
            "wb2" => new WeiterbildungCardDefinition(
                id: new CardId('wb2'),
                description: 'Ein Unternehmen verkauft 120 Fahrräder zu je 230 €. Die Gesamtkosten betragen 26.000 €. Der Umsatz beträgt 1.600 €.',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Falsch', true),
                    new AnswerOption(new AnswerId("c"), 'Wahr'),
                ],
            ),
            "wb3" => new WeiterbildungCardDefinition(
                id: new CardId('wb3'),
                description: 'Welche der folgenden Optionen ist KEIN typisches Motiv für die Gründung eines Unternehmens?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Langfristige Arbeitsplatzgarantie', true),
                    new AnswerOption(new AnswerId("b"), 'Unabhängigkeit'),
                    new AnswerOption(new AnswerId("a"), 'Selbstverwirklichung'),
                    new AnswerOption(new AnswerId("d"), 'Nutzung von Marktchancen'),
                ],
            ),
            "wb4" => new WeiterbildungCardDefinition(
                id: new CardId('wb4'),
                description: 'Welches der folgenden Interessen ist KEIN typisches Interesse von Arbeitnehmerinnen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Maximale Kosteneffizienz und unternehmerische Flexibilität', true),
                    new AnswerOption(new AnswerId("a"), 'Angemessene Vergütung und soziale Absicherung'),
                    new AnswerOption(new AnswerId("d"), 'Tarifliche Entlohnung und Arbeitsplatzsicherheit'),
                ],
            ),
            "wb5" => new WeiterbildungCardDefinition(
                id: new CardId('wb5'),
                description: 'Welches Begriffspaar beschreibt das ökonomische Prinzip?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Minimalprinzip und Maximalprinzip', true),
                    new AnswerOption(new AnswerId("d"), 'Rentabilitätsprinzip und Effizienzprinzip'),
                    new AnswerOption(new AnswerId("b"), 'Opportunitätsprinzip und Investitionsprinzip'),
                    new AnswerOption(new AnswerId("c"), 'Gewinnprinzip und Sparprinzip'),
                ],
            ),
            "wb6" => new WeiterbildungCardDefinition(
                id: new CardId('wb6'),
                description: 'Ein Unternehmen verkauft 120 Fahrräder zu je 230 €. Die Gesamtkosten betragen 26.000 €. Der Gewinn beträgt 1.600 €.',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Wahr', true),
                    new AnswerOption(new AnswerId("c"), 'Falsch'),
                ],
            ),
            "wb7" => new WeiterbildungCardDefinition(
                id: new CardId('wb7'),
                description: 'Welche der folgenden Optionen sind mögliche Ursachen für Überschuldung?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Einkommensausfall und negative Geschäftsentwicklung', true),
                    new AnswerOption(new AnswerId("d"), 'Hohe laufende Einnahmen und starke Liquidität'),
                    new AnswerOption(new AnswerId("a"), 'Hohe Rücklagenbildung und starke Kapitalreserven'),
                ],
            ),
            "wb8" => new WeiterbildungCardDefinition(
                id: new CardId('wb8'),
                description: 'Welche Wirtschaftsordnung strebt die Bundesrepublik Deutschland an?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Soziale Marktwirtschaft', true),
                    new AnswerOption(new AnswerId("d"), 'Freie Marktwirtschaft'),
                    new AnswerOption(new AnswerId("b"), 'Zentrale Planwirtschaft'),
                    new AnswerOption(new AnswerId("c"), 'Selbstversorgungswirtschaft'),
                ],
            ),
            "wb9" => new WeiterbildungCardDefinition(
                id: new CardId('wb9'),
                description: 'Wer einen Kredit in kleineren Raten über eine längere Zeit zurückzahlt, zahlt insgesamt mehr Zinsen als bei schnellerer Rückzahlung.',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Wahr', true),
                    new AnswerOption(new AnswerId("d"), 'Falsch'),
                ],
            ),
            "wb10" => new WeiterbildungCardDefinition(
                id: new CardId('wb10'),
                description: 'Wie hoch sind die Jahreszinsen für ein Darlehen über 2.000 €, wenn ein Zinssatz von 5 % vereinbart wurde?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), '100 €', true),
                    new AnswerOption(new AnswerId("c"), '150 €'),
                    new AnswerOption(new AnswerId("a"), '200 €'),
                ],
            ),
            "wb11" => new WeiterbildungCardDefinition(
                id: new CardId('wb11'),
                description: 'Welche Aussage ist richtig?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Die Krankenversicherungsbeiträge sind je nach Krankenkasse unterschiedlich hoch.', true),
                    new AnswerOption(new AnswerId("a"), 'In der Regel werden die Sozialversicherungsbeiträge fast ausschließlich von Arbeitnehmerinnen aufgebracht.'),
                    new AnswerOption(new AnswerId("d"), 'Der Nettolohn ist häufig höher als der Bruttolohn.'),
                ],
            ),
            "wb12" => new WeiterbildungCardDefinition(
                id: new CardId('wb12'),
                description: 'Welche Interessen teilen Arbeitgeberinnen und Arbeitnehmerinnen am häufigsten?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Arbeitsplatzsicherheit und langfristiger Erfolg', true),
                    new AnswerOption(new AnswerId("d"), 'Mehr Arbeitszeit und weniger Lohn'),
                    new AnswerOption(new AnswerId("c"), 'Weniger Belastung und niedrigere Kosten'),
                    new AnswerOption(new AnswerId("b"), 'Bessere Bedingungen und hohe Produktivität'),
                ],
            ),
            "wb13" => new WeiterbildungCardDefinition(
                id: new CardId('wb13'),
                description: 'Angebot und Nachfrage - Welche Aussage ist richtig?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Mit steigendem Preis eines Gutes sinkt laut Nachfragegesetz die Nachfrage.', true),
                    new AnswerOption(new AnswerId("b"), 'Bei einem Nachfrageüberhang wird von einem Gut weniger nachgefragt als verfügbar.'),
                    new AnswerOption(new AnswerId("d"), 'Bei einem Angebotsüberhang steigt der Preis eines Gutes.'),
                ],
            ),
            "wb14" => new WeiterbildungCardDefinition(
                id: new CardId('wb14'),
                description: 'Welche Faktoren beeinflussen die Nachfrage?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Preis des Gutes und Einkommen der Konsumentinnen', true),
                    new AnswerOption(new AnswerId("b"), 'Marktgröße und Wettbewerbsverhältnisse'),
                    new AnswerOption(new AnswerId("c"), 'Technologie und Innovationskraft'),
                    new AnswerOption(new AnswerId("d"), 'Produktionskosten und Ressourcenverfügbarkeit'),
                ],
            ),
            "wb15" => new WeiterbildungCardDefinition(
                id: new CardId('wb15'),
                description: 'Wie heißt der Preis, bei dem Angebot und Nachfrage genau übereinstimmen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Gleichgewichtspreis', true),
                    new AnswerOption(new AnswerId("d"), 'Höchstpreis'),
                    new AnswerOption(new AnswerId("c"), 'Mindestpreis'),
                ],
            ),
            "wb16" => new WeiterbildungCardDefinition(
                id: new CardId('wb16'),
                description: 'Welche Aussage trifft NICHT zu?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'Variable Kosten sind unabhängig von der Produktionsmenge, wenn der Fixkostenanteil hoch ist.', true),
                    new AnswerOption(new AnswerId("c"), 'Variable Kosten können mit der Produktionsmenge steigen.'),
                    new AnswerOption(new AnswerId("a"), 'Variable Kosten sind abhängig von der produzierten Stückzahl.'),
                    new AnswerOption(new AnswerId("b"), 'Variable Kosten verändern sich bei steigender Produktionsmenge.'),
                ],
            ),
            "wb17" => new WeiterbildungCardDefinition(
                id: new CardId('wb17'),
                description: 'Welche Formel entspricht dem wirtschaftlichen Begriff „Umsatz“?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Umsatz = Menge × Preis', true),
                    new AnswerOption(new AnswerId("b"), 'Umsatz = Gewinn – Steuern'),
                    new AnswerOption(new AnswerId("c"), 'Umsatz = Kosten × Preis'),
                    new AnswerOption(new AnswerId("d"), 'Umsatz = Menge + Kosten'),
                ],
            ),
            "wb18" => new WeiterbildungCardDefinition(
                id: new CardId('wb18'),
                description: 'Ein Annuitätenkredit wird mit konstanten Zahlungen aus Zins und Tilgung zurückgezahlt. Nach der Hälfte der Laufzeit ist die Restschuld meist geringer als die Hälfte des ursprünglichen Kredits.',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'Falsch', true),
                    new AnswerOption(new AnswerId("c"), 'Wahr'),
                ],
            ),
            "wb19" => new WeiterbildungCardDefinition(
                id: new CardId('wb19'),
                description: 'Welcher der folgenden Indikatoren misst den Wohlstand alternativ zum BIP (Bruttoinlandsprodukt)?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Human Development Index', true),
                    new AnswerOption(new AnswerId("a"), 'Inflationsrate'),
                    new AnswerOption(new AnswerId("b"), 'Pro-Kopf-Einkommen'),
                    new AnswerOption(new AnswerId("d"), 'Arbeitslosenquote'),
                ],
            ),
            "wb20" => new WeiterbildungCardDefinition(
                id: new CardId('wb20'),
                description: 'Was beschreibt den Unterschied zwischen nominalem und realem BIP?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'Nominales BIP nutzt aktuelle Preise, reales berücksichtigt Preisänderungen.', true),
                    new AnswerOption(new AnswerId("c"), 'Nominales BIP nutzt konstante Preise, reales aktuelle.'),
                    new AnswerOption(new AnswerId("b"), 'Das nominale BIP wird um die Bevölkerungszahl bereinigt, das reale nicht.'),
                ],
            ),
            "wb21" => new WeiterbildungCardDefinition(
                id: new CardId('wb21'),
                description: 'Welche Interessen vertreten Arbeitgeberinnen typischerweise?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Kostensenkung und flexible Personalplanung', true),
                    new AnswerOption(new AnswerId("d"), 'Hohe Löhne und maximale Sicherheit'),
                    new AnswerOption(new AnswerId("b"), 'Mehr Urlaub und kürzere Arbeitszeit'),
                    new AnswerOption(new AnswerId("c"), 'Mitbestimmung und Arbeitsplatzgarantie'),
                ],
            ),
            "wb22" => new WeiterbildungCardDefinition(
                id: new CardId('wb22'),
                description: 'Was versteht man unter dem Gewinn in der Betriebswirtschaft?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Die Differenz zwischen Umsatz und Kosten', true),
                    new AnswerOption(new AnswerId("d"), 'Die Summe aus Umsatz und Kosten'),
                    new AnswerOption(new AnswerId("a"), 'Der Umsatz ohne Abzüge'),
                    new AnswerOption(new AnswerId("c"), 'Die Menge verkaufter Produkte'),
                ],
            ),
            "wb23" => new WeiterbildungCardDefinition(
                id: new CardId('wb23'),
                description: 'Was kann ein Nachteil der sozialen Marktwirtschaft sein?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Hohe Staatsausgaben und mögliche Bürokratie', true),
                    new AnswerOption(new AnswerId("b"), 'Kein Sozialsystem, völlig freier Markt'),
                    new AnswerOption(new AnswerId("c"), 'Vollständige staatliche Preiskontrolle'),
                ],
            ),
            "wb24" => new WeiterbildungCardDefinition(
                id: new CardId('wb24'),
                description: 'Welche Faktoren bestimmen die Höhe von Löhnen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Qualifikation der Arbeitnehmenden und Branchenentwicklung', true),
                    new AnswerOption(new AnswerId("a"), 'Individuelle Sparziele und betriebliche Sozialangebote'),
                    new AnswerOption(new AnswerId("b"), 'Subjektive Zufriedenheit der Arbeitgebenden und Anzahl der Urlaubstage'),
                ],
            ),
            "wb25" => new WeiterbildungCardDefinition(
                id: new CardId('wb25'),
                description: 'Welche Aussage trifft auf ein Sparbuch zu?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Ein- und Auszahlung sind grundsätzlich kostenfrei.', true),
                    new AnswerOption(new AnswerId("a"), 'Hoher Zins, aber Kursrisiko.'),
                    new AnswerOption(new AnswerId("d"), 'Niedriger Zins, aber für den Zahlungsverkehr nutzbar.'),
                ],
            ),
            "wb26" => new WeiterbildungCardDefinition(
                id: new CardId('wb26'),
                description: 'Was passiert, wenn der Leitzins der Europäischen Zentralbank steigt?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Kredite werden teurer, Sparzinsen steigen', true),
                    new AnswerOption(new AnswerId("d"), 'Kredite werden günstiger, Sparzinsen sinken'),
                    new AnswerOption(new AnswerId("c"), 'Der Euro verliert automatisch an Wert'),
                ],
            ),
            "wb27" => new WeiterbildungCardDefinition(
                id: new CardId('wb27'),
                description: 'Welche Anlageform gilt als besonders sicher, aber mit niedrigerer Rendite?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Tagesgeldkonto', true),
                    new AnswerOption(new AnswerId("d"), 'Kryptowährungen'),
                    new AnswerOption(new AnswerId("b"), 'Aktienfonds'),
                ],
            ),
            "wb28" => new WeiterbildungCardDefinition(
                id: new CardId('wb28'),
                description: 'Welche Strategie dient zur Risikominimierung bei Investitionen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Diversifikation des Portfolios', true),
                    new AnswerOption(new AnswerId("d"), 'Kredite aufnehmen, um mehr investieren zu können'),
                    new AnswerOption(new AnswerId("a"), 'Alle Investitionen in eine einzige Aktie stecken'),
                ],
            ),
            "wb29" => new WeiterbildungCardDefinition(
                id: new CardId('wb29'),
                description: 'Was bedeutet „progressive Besteuerung“?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Der Steuersatz steigt mit zunehmendem Einkommen.', true),
                    new AnswerOption(new AnswerId("a"), 'Alle zahlen denselben Prozentsatz ihres Einkommens.'),
                    new AnswerOption(new AnswerId("b"), 'Der Staat erhebt nur Steuern auf hohe Erbschaften.'),
                ],
            ),
            "wb30" => new WeiterbildungCardDefinition(
                id: new CardId('wb30'),
                description: 'Was beeinflusst typischerweise eine Investitionsentscheidung einer Privatperson am meisten?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Risiko der Investition und erwartete Rendite', true),
                    new AnswerOption(new AnswerId("b"), 'Steuern und Arbeitsstunden'),
                    new AnswerOption(new AnswerId("d"), 'Politische Stabilität und Unternehmensgröße'),
                    new AnswerOption(new AnswerId("c"), 'Gehalt und Urlaubsanspruch'),
                ],
            ),
            "wb31" => new WeiterbildungCardDefinition(
                id: new CardId('wb31'),
                description: 'Wie unterscheidet sich eine Aktie von einer Anleihe?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'Eine Aktie ist ein Unternehmensanteil, während eine Anleihe ein Darlehen an ein Unternehmen ist.', true),
                    new AnswerOption(new AnswerId("a"), 'Eine Aktie garantiert feste Zinsen.'),
                    new AnswerOption(new AnswerId("c"), 'Eine Aktie kann nur bei der Unternehmensgründung erworben werden.'),
                    new AnswerOption(new AnswerId("b"), 'Eine Aktie bringt immer eine höhere Rendite.'),
                ],
            ),
            "wb32" => new WeiterbildungCardDefinition(
                id: new CardId('wb32'),
                description: 'Welche der folgenden Optionen beschreibt ein Risiko bei Geldanlagen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'Inflation und mögliche Wertverluste', true),
                    new AnswerOption(new AnswerId("a"), 'Langfristige Sicherheit'),
                    new AnswerOption(new AnswerId("c"), 'Steuerersparnisse'),
                    new AnswerOption(new AnswerId("b"), 'Hohe Renditen ohne Unsicherheit'),
                ],
            ),
            "wb33" => new WeiterbildungCardDefinition(
                id: new CardId('wb33'),
                description: 'Was unterscheidet kurzfristige von langfristigen Investitionen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Kurzfristige Investitionen haben eine kürzere Laufzeit und bieten schnellere Rückflüsse.', true),
                    new AnswerOption(new AnswerId("a"), 'Kurzfristige Investitionen werden nur in Aktien getätigt, langfristige nur in Immobilien.'),
                    new AnswerOption(new AnswerId("d"), 'Kurzfristige Investitionen sind nur für Unternehmen sinnvoll, langfristige nur für Privatpersonen.'),
                ],
            ),
            "wb34" => new WeiterbildungCardDefinition(
                id: new CardId('wb34'),
                description: 'Was ist der Zinseszinseffekt?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Zinsen werden auf bereits erhaltene Zinsen berechnet.', true),
                    new AnswerOption(new AnswerId("d"), 'Zinsen steigen immer jährlich.'),
                    new AnswerOption(new AnswerId("c"), 'Zinsen werden nur auf das ursprüngliche Kapital berechnet.'),
                ],
            ),
            "wb35" => new WeiterbildungCardDefinition(
                id: new CardId('wb35'),
                description: 'Was bedeutet „Liquidität“ in Bezug auf Investitionen?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Die Geschwindigkeit, mit der eine Investition verkauft werden kann.', true),
                    new AnswerOption(new AnswerId("a"), 'Der Gewinn, den eine Investition am Ende der Laufzeit abwirft.'),
                    new AnswerOption(new AnswerId("d"), 'Der langfristig erwartete Wertzuwachs einer Investition.'),
                ],
            ),
            "wb36" => new WeiterbildungCardDefinition(
                id: new CardId('wb36'),
                description: 'Welche Aussage beschreibt am besten eine Anleihe?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Festverzinsliches Wertpapier, mit dem man einem Staat Geld leiht', true),
                    new AnswerOption(new AnswerId("a"), 'Unternehmensanteil mit Anspruch auf Gewinnbeteiligung'),
                    new AnswerOption(new AnswerId("d"), 'Eine kurzfristige Investition in einzelne Aktien'),
                ],
            ),
            "wb37" => new WeiterbildungCardDefinition(
                id: new CardId('wb37'),
                description: 'Wie unterscheiden sich ETFs von traditionellen Investmentfonds?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'ETFs sind meist passiv und bilden einen Index ab', true),
                    new AnswerOption(new AnswerId("b"), 'ETFs werden nicht börslich gehandelt'),
                    new AnswerOption(new AnswerId("a"), 'ETFs haben höhere Verwaltungskosten'),
                ],
            ),
            "wb38" => new WeiterbildungCardDefinition(
                id: new CardId('wb38'),
                description: 'Welche der folgenden Aussagen beschreibt am besten einen ETF?',
                answerOptions: [
                    new AnswerOption(new AnswerId("b"), 'Ein passiv verwalteter Fonds, der einen Index nachbildet.', true),
                    new AnswerOption(new AnswerId("c"), 'Ein aktiver Fonds, der nur in einzelne Aktien investiert.'),
                    new AnswerOption(new AnswerId("a"), 'Ein Investment in Immobilien.'),
                ],
            ),
            "wb39" => new WeiterbildungCardDefinition(
                id: new CardId('wb39'),
                description: 'Was passiert in einem Markt mit hoher Nachfrage und geringem Angebot?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Der Preis steigt', true),
                    new AnswerOption(new AnswerId("d"), 'Der Preis bleibt konstant'),
                    new AnswerOption(new AnswerId("a"), 'Der Preis sinkt'),
                ],
            ),
            "wb40" => new WeiterbildungCardDefinition(
                id: new CardId('wb40'),
                description: 'Was passiert nach dem Nachfragegesetz typischerweise mit der nachgefragten Menge eines Gutes, wenn dessen Preis steigt?',
                answerOptions: [
                    new AnswerOption(new AnswerId("a"), 'Die Nachfrage sinkt', true),
                    new AnswerOption(new AnswerId("d"), 'Die Nachfrage bleibt unverändert'),
                    new AnswerOption(new AnswerId("b"), 'Die Nachfrage steigt'),
                ],
            ),
            "wb41" => new WeiterbildungCardDefinition(
                id: new CardId('wb41'),
                description: 'Welche Faktoren bestimmen das Angebot?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), 'Produktionskosten und Ressourcenverfügbarkeit', true),
                    new AnswerOption(new AnswerId("a"), 'Verbraucherpräferenzen und saisonale Trends'),
                    new AnswerOption(new AnswerId("b"), 'Preispolitik und staatliche Eingriffe'),
                    new AnswerOption(new AnswerId("c"), 'Einkommen der Konsumentinnen'),
                ],
            ),
            "wb42" => new WeiterbildungCardDefinition(
                id: new CardId('wb42'),
                description: 'Welche Aussage beschreibt eine Einschränkung des einfachen Wirtschaftskreislaufs am treffendsten?',
                answerOptions: [
                    new AnswerOption(new AnswerId("c"), 'Er ignoriert institutionelle Sektoren wie Staat, Finanzsystem und Ausland.', true),
                    new AnswerOption(new AnswerId("b"), 'Er bildet nur den realen Güterstrom, nicht aber monetäre Transaktionen ab.'),
                    new AnswerOption(new AnswerId("d"), 'Er berücksichtigt lediglich die Rolle von Konsumenten, nicht aber von Produzenten.'),
                ],
            ),
        ], self::getLegacyCards());
        return self::$instance;
    }

    /**
     * Cards that were removed from the game with a new import of the card definitions.
     *
     * WHY: The events of a game only store the ids of the cards. Games that were started before the import still
     * reference these cards and need them to be loaded, e.g. to show the game log or to export the game as json
     * (see GameResource). These cards are never drawn in new games, because they are not part of {@see self::$cards}.
     *
     * They can be deleted once the games started before the respective import are not needed anymore.
     *
     * @return CardDefinition[]
     */
    private static function getLegacyCards(): array
    {
        return [
            // removed with the import of the final import files (2026-09)
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
                    EreignisPrerequisitesId::HAS_CHILD,
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
            "wb43" => new WeiterbildungCardDefinition(
                id: new CardId('wb43'),
                description: 'Welche Aussage beschreibt eine Einschränkung des einfachen Wirtschaftskreislaufs am treffendsten?',
                answerOptions: [
                    new AnswerOption(new AnswerId("d"), "Er ignoriert institutionelle Sektoren wie Staat, Finanzsystem und Ausland.", true),
                    new AnswerOption(new AnswerId("a"), "Er bildet nur den realen Güterstrom, nicht aber monetäre Transaktionen ab."),
                    new AnswerOption(new AnswerId("b"), "Er berücksichtigt lediglich die Rolle von Konsumenten, nicht aber von Produzenten."),
                ],
            ),
        ];
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
        $card = $this->cards[$cardId->value] ?? $this->legacyCards[$cardId->value] ?? null;
        if ($card === null) {
            throw new \RuntimeException('Card ' . $cardId . ' does not exist', 1747645954);
        }

        assert($card instanceof $classString);
        if (!$card instanceof $classString) {
            throw new \RuntimeException(
                'Card ' . $cardId . ' expected to be of type ' . $classString . ' but was ' . get_class($card),
                1752499517
            );
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
        return array_filter($this->cards, fn ($card) => $card->getCategory()->value === $categoryId->value &&
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
