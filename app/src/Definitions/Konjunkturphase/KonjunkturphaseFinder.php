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
        $konjunkturphase1 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(1),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung I – Erste Erholung',
            description: 'Nachdem eine globale Krise die internationalen Lieferketten stark gestört hatte, ist der Konsum jedoch noch verhalten,
            da Haushalte und Unternehmen vorsichtig agieren. Unternehmen beginnen, ihre Lager aufzufüllen und Neueinstellungen zu tätigen. Die Zentralbank
            hält den Leitzins daher mit 1 % niedrig, um günstige Kredite zu ermöglichen und Investitionen sowie Konsumausgaben zu begünstigen. Dadurch
            bleiben Kredite günstig und die Unternehmen sowie Haushalte können leichter investieren und konsumieren.',
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
            ],
        );

        $konjunkturphase2 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(2),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung II – Stabile Expansion',
            description: 'Ein staatliches Infrastrukturpaket sorgt für  wirtschaftlichen Schwung. Straßen, Bahnlinien und digitale
            Netze werden ausgebaut und es entstehen neue Jobs. Die Konjunktur festigt sich zunehmend und die Zentralbank reagiert
            vorsichtig. Sie erhöht den Leitzins auf 1,5 %, um zukünftigen Inflationsrisiken vorzubeugen. Kredite bleiben jedoch
            weiterhin attraktiv, sodass der Aufschwung nachhaltig unterstützt wird.',
            additionalEvents: 'Infrastrukturprogramm: Immobilienkauf und -verkauf +10 %', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 4.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.50
                ),
            ]
        );

        $konjunkturphase3 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(3),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung III – Kräftiges Wachstum',
            description: 'Neue technologische Innovationen führen zu deutlich höherer Produktivität und neuen Wachstumsimpulsen. Unternehmen investieren
            in Zukunftstechnologien und schaffen viele Arbeitsplätze. Weil die Wirtschaft nun robust wächst, hebt die Zentralbank den Leitzins auf 2 % an,
            um das Wachstum  zu begleiten und einer möglichen Überhitzung entgegenzuwirken. Kredite bleiben moderat teuer, trotz steigender Zinsen
            investieren jedoch Unternehmen weiter, da die Renditeerwartungen bei Investitionen in Zukunftstechnologien hoch sind.',
            additionalEvents: 'Einmalige Gehaltssonderzahlung i.H.v. 1000 €',
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.50
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_JOB,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(+1000)),
                )
            ],
        );

        $konjunkturphase4 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(4),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung IV – Späte Phase',
            description: 'Die Nachfrage nach Exportprodukten ist hoch, aber Fachkräfte und Rohstoffe werden zunehmend knapp. Unternehmen stoßen an ihre
            Kapazitätsgrenzen, was steigende Löhne und erste Inflationssignale zur Folge hat. Die Zentralbank greift nun entschiedener ein und hebt den
            Leitzins auf 2,5 % an, um die Wirtschaft sanft auszubremsen und eine Überhitzung zu verhindern. Dies führt zu höheren Kreditkosten, was die
            Investitionen erstmals etwas erschwert.',
            additionalEvents: 'Einmalig 500 € für steigende Lebensmittelpreise',
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
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 5.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.6
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(+500)),
                )
            ],
        );

        $konjunkturphase5 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(5),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom I - Frühe Expansion',
            description: 'Niedrige Zinssätze der letzten Jahre führen dazu, dass Unternehmen und Verbraucher weiterhin großzügig investieren und konsumieren.
            Die Wirtschaft wächst stabil, die Stimmung bleibt optimistisch, und Arbeitsplätze sind sicher. Die Zentralbank erkennt die gute Lage und
            stabilisiert den Leitzins bei 2 %, sodass der Kreditzins weiterhin attraktiv bleibt.',
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.6
                ),
            ]
        );

        $konjunkturphase6 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(6),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom II - Mittlere Expansion',
            description: 'Ein globaler Handelsboom sorgt für Rekordgewinne in Unternehmen und spürbar steigende Löhne. Die Kaufkraft der Haushalte
            wächst stark und viele Märkte expandieren. Da die Wirtschaft nun auf Hochtouren läuft und Inflationsrisiken steigen, hebt die Zentralbank
            den Leitzins auf 3 % an. Die höheren Kreditkosten bremsen Investitionen bisher jedoch kaum, da die Gewinne weiterhin hoch sind.',
            additionalEvents: 'Einmalige Lohnsonderzahlung i.H.v. 10 % des Einkommens', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.8
                ),
            ]
        );

        $konjunkturphase7 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(7),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom III - Überhitzung',
            description: 'Eine globale Rohstoffknappheit treibt die Preise weltweit in die Höhe. Unternehmen haben Mühe, die steigenden Kosten weiterzugeben und
            erste Anzeichen einer Blasenbildung sind sichtbar. Spekulationen haben dazu geführt, dass Immobilienpreise zunehmend den Bezug zu den wirtschaftlichen
            Kennzahlen verloren haben und eine Immobilienblase ist entstanden. Die Zentralbank reagiert mit einer deutlichen Anhebung des Leitzinses auf 4 %, um
            die Inflation zu bekämpfen. Die merklich gestiegenen Kreditkosten führen bereits zu ersten negativen Auswirkungen auf neue Investitionen.',
            additionalEvents: 'Immobilienblase, für jede Immobilie fallen Steuern i.H.v. 1000 € pro Objekt an', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 7
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.8
                ),
            ]
        );

        $konjunkturphase8 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(8),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom IV - Asset-Blasé',
            description: 'Langjährig niedrige Zinsen haben spekulative Investitionen in Aktien, Immobilien und Kryptowährungen massiv ansteigen lassen.
            Die Preise sind stark überbewertet und weit von ihren fundamentalen Werten entfernt. Die Zentralbank zieht nun deutlich die geldpolitische
            Bremse und hebt den Leitzins auf 5  % an, was Kredite deutlich teurer macht. Experten warnen, dass die Wirtschaft sich am Rand einer Korrektur
            befindet und eine Rezession droht, falls ein unerwarteter Schock eintritt.',
            additionalEvents: 'Immobilienkauf und -verkauf +10 %, Einmalige Grundsteuer pro Immobilien i.H.v. 1000 € pro Objekt, 50 % Chance, das Rezession folgt', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::SOZIALES_UND_FREIZEIT,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 5),
                        new ZeitslotsPerPlayer(3, 6),
                        new ZeitslotsPerPlayer(4, 6),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::JOBS,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 10
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 2.1
                ),
            ]
        );

        $konjunkturphase9 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(9),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession I – Sanfte Abkühlung',
            description: 'Die Wirtschaft verliert leicht an Schwung, da internationale Handelskonflikte und leichte Nachfragerückgänge erste Spuren hinterlassen.
            Unternehmen investieren vorsichtiger und verschieben größere Projekte. Die Zentralbank erkennt die schwache Entwicklung und senkt den Leitzins auf
            moderate 1 %, wodurch Kredite günstig bleiben und ein stärkerer Abschwung verhindert werden soll. Der Staat reagiert mit einem Bildungsgutschein,
            um die Qualifikation der Arbeitnehmer zu verbessern.',
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.4
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(bildungKompetenzsteinChange: +1),
                )
            ],
        );

        $konjunkturphase10 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(10),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession II – Nachfragerückgang',
            description: 'Ein stärkerer Rückgang der Nachfrage belastet zunehmend die Wirtschaft. Immer mehr Unternehmen müssen Kurzarbeit anmelden,
            wodurch Arbeitszeit und Einkommen sinken. Die Zentralbank hält den Leitzins stabil niedrig bei 1 %, um weitere Schäden zu verhindern,
            doch die erhoffte Belebung bleibt vorerst aus. Die Unternehmen setzen aufgrund der schwierigen Lage Lohnsonderzahlungen aus, wodurch
            private Konsumausgaben zusätzlich belastet werden.',
            additionalEvents: 'Kurzarbeit, -1 Zeitstein wenn Erwerbseinkommen, Einkommen -5 % diese Runde', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: -5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.3
                ),
            ]
        );

        $konjunkturphase11 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(11),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession III – Nachfrageschwäche',
            description: 'Aufgrund eines anhaltenden Abschwungs bleibt die Stimmung in der Wirtschaft gedrückt. Unternehmen zeigen sich vorsichtig bei
            Neueinstellungen und Investitionen. Um die anhaltende Nachfrageschwäche abzumildern, senkt die Zentralbank den Leitzins auf 0,75 %, was zu
            historisch niedrigen Kreditkosten führt. Zusätzlich versucht die Regierung, die Konsumenten mit einem einmaligen Konjunkturbonus von 500 €
            pro Person zu unterstützen. Dafür trifft Immobilienbesitzer eine zusätzliche Grundsteuer.',
            additionalEvents: 'Konjunkturbonus i.H.v. 500 €, Grundsteuer pro Immobilie i.H.v. 500 €', //TODO implement (Grundsteuer)
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
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 3.75
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.2
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(+500)),
                )
            ],
        );

        $konjunkturphase12 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(12),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession IV – Kreditklemme',
            description: 'Banken werden aufgrund von Kreditausfällen zunehmend zurückhaltender werden. Unternehmen haben Schwierigkeiten, an frisches Geld zu
            kommen, wodurch viele Projekte vorerst gestoppt werden. Trotz einer Zinssenkung der Zentralbank auf 0,5 %, bleibt der Kreditmarkt angespannt.
            Darlehensnehmer spüren zusätzlich die Krise durch eine einmalige Extra-Zinszahlung, während Immobilienwerte unter Druck geraten.',
            additionalEvents: 'Einmaliger Extrazins für alle mit Darlehen i.H.v. 200 €, Immobilienkauf und -verkauf -5 %', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
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
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 3.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: -10
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 1.1
                ),
            ]
        );

        $konjunkturphase13 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(13),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression I – Einsetzen der Deflation',
            description: 'Die Wirtschaftskrise verschärft sich deutlich. Unternehmen finden kaum noch Abnehmer für ihre Produkte und Geschäfte reduzieren
            zunehmend ihre Preise, um damit Käufer anzulocken. Da immer weniger Menschen ihr Geld ausgeben, sinken die Preise weiter und es droht eine
            gefährliche Spirale. Die Zentralbank senkt die Zinsen nahezu auf null, doch die Zinssenkung zeigt kaum Wirkung. Die Verunsicherung am Markt
            lässt Immobilienpreise sinken.',
            additionalEvents: 'Immobilienkauf und -verkauf -10 %', //TODO implement
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
            ]
        );

        $konjunkturphase14 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(14),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression II – Bankenzusammenbruch',
            description: 'Es eskaliert eine Krise, als mehrere große Banken plötzlich kurz vor der Insolvenz stehen. Um das gesamte Finanzsystem vor dem
            Kollaps zu retten, stellt die Regierung die Banken unter Schutz und lässt Kredite vorübergehend einfrieren. Daraus resultiert eine Panik an
            den Märkten und Immobilienpreise und Aktienkurse brechen ein. Dies geschieht trotz des radikalen Eingriffs der Zentralbank, die den Leitzins
            vollständig auf null senkt.',
            additionalEvents: 'Es können keine Kredite aufgenommen werden, Immobilienkauf und verkauf -15 %, Einkommen -10 % diese Runde', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 3
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 0.9
                ),
            ]
        );

        $konjunkturphase15 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(15),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression III – Stagnationstal',
            description: 'Die Wirtschaft scheint am tiefsten Punkt einer Krise angekommen zu sein. Unternehmen zögern mit Investitionen und die Menschen
            sparen, statt ihr Geld auszugeben. Trotz massiver geldpolitischer Maßnahmen der Zentralbank und der Senkung des Leitzins auf 0 % bleibt die
            Stimmung gedrückt. Um die Nachfrage kurzfristig anzukurbeln, verteilt der Staat eine einmalige finanzielle Unterstützung an alle Bürger.',
            additionalEvents: 'Konjunkturbonus i.H.v. 500 € p.P.',
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 3
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: 0
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 0.9
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(+500)),
                )
            ],
        );

        $konjunkturphase16 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(16),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression IV – Zäher Boden',
            description: 'Eine lange Krise hat tiefe Spuren hinterlassen. Viele Haushalte sind überschuldet und Unternehmen kämpfen weiterhin
            ums Überleben. Die Zentralbank hält den Leitzins auf null Prozent und sorgt dafür, dass Kredite billig bleiben. Politik und Banken
            einigen sich auf einen Schuldenerlass, um die finanziellen Belastungen zu mildern. Infolge dieser Maßnahmen kehrt allmählich
            Vertrauen in die Wirtschaft zurück und zuvor fallende Kurse beginnen sich zu stabilisieren.',
            additionalEvents: 'Einmalig für offene Darlehen -1000 €, Immobilienkauf und -verkauf -5 %', //TODO implement
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
            ],
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    modifier: 3
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    modifier: -5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    modifier: 0.9
                ),
            ]
        );

        self::$instance = new self([
            $konjunkturphase1,
            $konjunkturphase2,
            $konjunkturphase3,
            $konjunkturphase4,
            $konjunkturphase5,
            $konjunkturphase6,
            $konjunkturphase7,
            $konjunkturphase8,
            $konjunkturphase9,
            $konjunkturphase10,
            $konjunkturphase11,
            $konjunkturphase12,
            $konjunkturphase13,
            $konjunkturphase14,
            $konjunkturphase15,
            $konjunkturphase16,
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
