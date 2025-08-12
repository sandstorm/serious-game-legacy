<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\Dto\Zeitslots;
use Domain\Definitions\Konjunkturphase\Dto\ZeitslotsPerPlayer;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\Dto\ZeitsteinePerPlayer;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Random\Randomizer;

class KonjunkturphaseFinder
{
    /**
     * @var KonjunkturphaseDefinition[]
     */
    private array $konjunkturphaseDefinitions;

    private static ?self $instance = null;

    /**
     * @param KonjunkturphaseDefinition[] $konjunkturphaseDefinitions
     */
    private function __construct(array $konjunkturphaseDefinitions)
    {
        $this->konjunkturphaseDefinitions = $konjunkturphaseDefinitions;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            return self::initialize();
        }
        return self::$instance;
    }

    /**
     * @param KonjunkturphaseDefinition[] $konjunkturphaseDefinitions
     * @return void
     */
    public function overrideKonjunkturphaseDefinitionsForTesting(array $konjunkturphaseDefinitions): void
    {
        self::getInstance()->konjunkturphaseDefinitions = $konjunkturphaseDefinitions;
    }

    private static function initialize(): self
    {
        $year1 = new KonjunkturphaseDefinition(
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
                        new ZeitslotsPerPlayer(2, 5),
                        new ZeitslotsPerPlayer(3, 6),
                        new ZeitslotsPerPlayer(4, 6),
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
            ],
        );

        $year2 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(2),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Frühe Expansion',
            description: 'Niedrige Zinssätze der letzten Jahre führen dazu, dass Unternehmen und Verbraucher weiterhin großzügig investieren und konsumieren. Die Wirtschaft wächst stabil, die Stimmung bleibt optimistisch, und Arbeitsplätze sind sicher. Die Zentralbank erkennt die gute Lage und stabilisiert den Leitzins bei 2 %, sodass der Kreditzins weiterhin attraktiv bleibt.',
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.60
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    modifier: 13
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BONUS_INCOME,
                    modifier: 0
                ),
            ]
        );

        $year3 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(3),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Sanfte Abkühlung',
            description: 'Die Wirtschaft verliert leicht an Schwung, da internationale Handelskonflikte und leichte Nachfragerückgänge erste Spuren hinterlassen. Unternehmen investieren vorsichtiger und verschieben größere Projekte. Die Zentralbank erkennt die schwache Entwicklung und senkt den Leitzins auf moderate 1  %, wodurch Kredite günstig bleiben und ein stärkerer Abschwung verhindert werden soll. Der Staat reagiert mit einem Bildungsgutschein, um die Qualifikation der Arbeitnehmer zu verbessern.',
            additionalEvents: 'Bildungs-Bonus: 1 Bildungs- & Karrierepunkt',
            zeitsteine: new Zeitsteine(
                [
                    new ZeitsteinePerPlayer(2, 5),
                    new ZeitsteinePerPlayer(3, 4),
                    new ZeitsteinePerPlayer(4, 4),
                ]
            ),
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 5),
                        new ZeitslotsPerPlayer(3, 6),
                        new ZeitslotsPerPlayer(4, 6),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::ZEITSTEINE,
                    modifier: 0,
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
                    modifier: -6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BONUS_INCOME,
                    modifier: 0
                ),
            ]
        );

        $year4 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(4),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Einsetzen der Deflation',
            description: 'Die Wirtschaftskrise verschärft sich deutlich. Unternehmen finden kaum noch Abnehmer für ihre Produkte und Geschäfte reduzieren zunehmend ihre Preise, um damit Käufer anzulocken. Da immer weniger Menschen ihr Geld ausgeben, sinken die Preise weiter und es droht eine gefährliche Spirale. Die Zentralbank senkt die Zinsen nahezu auf null, doch die Zinssenkung zeigt kaum Wirkung. Die Verunsicherung am Markt lässt Immobilienpreise sinken.',
            additionalEvents: 'Immobilienkauf und -verkauf -10 %',
            zeitsteine: new Zeitsteine(
                [
                    new ZeitsteinePerPlayer(2, 4),
                    new ZeitsteinePerPlayer(3, 3),
                    new ZeitsteinePerPlayer(4, 3),
                ]
            ),
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 5),
                        new ZeitslotsPerPlayer(3, 6),
                        new ZeitslotsPerPlayer(4, 6),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
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
                    modifier: -1,
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LEBENSERHALTUNGSKOSTEN,
                    modifier: 95
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BILDUNG,
                    modifier: 95
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::FREIZEIT,
                    modifier: 95
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 3.25
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: -15
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 0.95
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    modifier: -10
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    modifier: -22
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::BONUS_INCOME,
                    modifier: 0
                ),
            ]
        );

        self::$instance = new self([
            $year1,
            $year2,
            $year3,
            $year4,
        ]);

        return self::$instance;
    }

    /**
     * @return KonjunkturphaseDefinition[]
     */
    public static function getAllKonjunkturphasen(): array
    {
        return self::getInstance()->konjunkturphaseDefinitions;
    }

    /**
     * returns a random Konjunkturphase
     *
     * @param KonjunkturphaseTypeEnum|null $lastType
     * @return KonjunkturphaseDefinition
     */
    public static function getRandomKonjunkturphase(?KonjunkturphaseTypeEnum $lastType): KonjunkturphaseDefinition
    {
        $possibleNextPhaseTypes = self::getListOfPossibleNextPhaseTypes($lastType);
        $konjunkturphasen = self::getAllKonjunkturphasenByTypes($possibleNextPhaseTypes);

        $randomizer = new Randomizer();
        return $randomizer->shuffleArray($konjunkturphasen)[0];
    }

    /**
     * @param KonjunkturphaseTypeEnum[] $types
     * @return KonjunkturphaseDefinition[]
     */
    public static function getAllKonjunkturphasenByTypes(array $types): array
    {
        $allKonjunkturphasen = self::getAllKonjunkturphasen();
        return array_filter($allKonjunkturphasen, static fn(KonjunkturphaseDefinition $konjunkturphase) => in_array($konjunkturphase->type, $types, true));
    }

    /**
     * @param KonjunkturphasenId $id
     * @return KonjunkturphaseDefinition
     */
    public static function findKonjunkturphaseById(KonjunkturphasenId $id): KonjunkturphaseDefinition
    {
        $konjunkturphasen = self::getAllKonjunkturphasen();
        foreach ($konjunkturphasen as $konjunkturphase) {
            if ($konjunkturphase->id === $id) {
                return $konjunkturphase;
            }
        }
        throw new \InvalidArgumentException('Konjunkturphase not found');
    }

    /**
     * Public for testing purposes only.
     * Returns a list of possible next Konjunkturphasen types based on the current type.
     *
     * @param KonjunkturphaseTypeEnum|null $konjunkturphaseType
     * @return KonjunkturphaseTypeEnum[]
     * @internal
     */
    public static function getListOfPossibleNextPhaseTypes(?KonjunkturphaseTypeEnum $konjunkturphaseType = null): array
    {
        return match ($konjunkturphaseType) {
            KonjunkturphaseTypeEnum::AUFSCHWUNG => [
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
                KonjunkturphaseTypeEnum::BOOM,
                KonjunkturphaseTypeEnum::REZESSION,
            ],
            KonjunkturphaseTypeEnum::BOOM => [
                KonjunkturphaseTypeEnum::BOOM,
                KonjunkturphaseTypeEnum::DEPRESSION,
                KonjunkturphaseTypeEnum::REZESSION,
            ],
            KonjunkturphaseTypeEnum::REZESSION => [
                KonjunkturphaseTypeEnum::REZESSION,
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
                KonjunkturphaseTypeEnum::DEPRESSION,
            ],
            KonjunkturphaseTypeEnum::DEPRESSION => [
                KonjunkturphaseTypeEnum::DEPRESSION,
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
            ],
            default => [
                KonjunkturphaseTypeEnum::AUFSCHWUNG,
            ]
        };
    }
}
