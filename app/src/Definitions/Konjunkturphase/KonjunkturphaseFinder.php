<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\ValueObject\ModifierId;
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
     * Resets the definitions (e.g. after they were overridden by a test).
     * Calling `...ForTesting` functions outside of test code will be caught by phpstan.
     */
    public static function initializeForTesting(): void
    {
        self::initialize();
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
            description: 'Eine globale Krise hat die internationalen Lieferketten stark gestört. Der Konsum ist weiterhin verhalten, da Haushalte und Unternehmen vorsichtig agieren. Die Unternehmen beginnen jedoch, ihre Lager aufzufüllen und neue Beschäftigte einzustellen. Die Zentralbank hält den Leitzins mit 1 % niedrig, um günstige Kredite zu ermöglichen und dadurch Investitionen sowie Konsumausgaben zu fördern.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 6),
                new ZeitsteinePerPlayer(3, 5),
                new ZeitsteinePerPlayer(4, 5),
            ]),
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
            modifierIds: [
            ],
            modifierParameters: new ModifierParameters(
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 10
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 2
                ),
            ],
            conditionalResourceChanges: [
            ],
            zeitsteineDescription: '+1 Zeitstein für alle',
        );

        $konjunkturphase2 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(2),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung II – Stabile Expansion',
            description: 'Ein staatliches Infrastrukturpaket sorgt für wirtschaftlichen Schwung. Straßen, Bahnlinien und digitale Netze werden ausgebaut, wodurch neue Arbeitsplätze entstehen. Die Konjunktur festigt sich zunehmend, und die Zentralbank reagiert vorsichtig. Sie erhöht den Leitzins auf 1,5 %, um zukünftigen Inflationsrisiken vorzubeugen. Die Kreditzinsen bleiben dennoch attraktiv, sodass der Aufschwung weiterhin unterstützt wird.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
            ],
            modifierIds: [
            ],
            modifierParameters: new ModifierParameters(
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 4.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 7
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 15
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 10
                ),
            ],
            conditionalResourceChanges: [
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase3 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(3),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung III – Kräftiges Wachstum',
            description: 'Neue technologische Innovationen führen zu einer deutlich höheren Produktivität und setzen neue Wachstumsimpulse. Unternehmen investieren in Zukunftstechnologien und schaffen zahlreiche Arbeitsplätze. Weil die Wirtschaft nun robust wächst, hebt die Zentralbank den Leitzins auf 2 % an, um einer möglichen Überhitzung entgegenzuwirken. Die Kredite verteuern sich dadurch moderat. Trotz steigender Zinsen investieren die Unternehmen jedoch weiter, da die erwarteten Renditen von Zukunftstechnologien hoch sind.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
            ],
            modifierIds: [
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyKostenBildungUndKarrierePercent:105,
                modifyKostenSozialesUndFreizeitPercent:105,
                modifyLebenshaltungskostenMultiplier:105,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 20
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 4
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_JOB,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(1000)),
                    description: 'Einmalige Gehaltssonderzahlung i.H.v. 1000 €',
                ),
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase4 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(4),
            type: KonjunkturphaseTypeEnum::AUFSCHWUNG,
            name: 'Aufschwung IV – Späte Phase',
            description: 'Die Nachfrage nach Exportprodukten ist hoch, doch Fachkräfte und Rohstoffe werden zunehmend knapp. Unternehmen stoßen an ihre Kapazitätsgrenzen, was steigende Löhne und erste Anzeichen von Inflation zur Folge hat. Die Zentralbank greift nun entschiedener ein und hebt den Leitzins auf 2,5 % an, um die Wirtschaft sanft abzubremsen und eine Überhitzung zu verhindern. Die dadurch steigenden Kreditkosten erschweren erstmals die Finanzierung neuer Investitionen.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 4),
                new ZeitsteinePerPlayer(3, 3),
                new ZeitsteinePerPlayer(4, 3),
            ]),
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
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
            modifierIds: [
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyKostenBildungUndKarrierePercent:105,
                modifyKostenSozialesUndFreizeitPercent:105,
                modifyLebenshaltungskostenMultiplier:105,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 5.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 24
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 3
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-500)),
                    description: 'Einmalig 500 € für steigende Lebensmittelpreise',
                ),
            ],
            zeitsteineDescription: '-1 Zeitstein für alle',
        );

        $konjunkturphase5 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(5),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom I – Frühe Expansion',
            description: 'Die niedrigen Zinssätze der vergangenen Jahre führen dazu, dass Unternehmen und Haushalte weiterhin umfangreich investieren und konsumieren. Die Wirtschaft wächst stabil, die Stimmung bleibt optimistisch und die Arbeitsplätze gelten als sicher. Angesichts der guten wirtschaftlichen Lage belässt die Zentralbank den Leitzins bei 2 %, sodass Kredite weiterhin zu attraktiven Konditionen verfügbar sind.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 6),
                new ZeitsteinePerPlayer(3, 5),
                new ZeitsteinePerPlayer(4, 5),
            ]),
            kompetenzbereiche: [
                new KompetenzbereichDefinition(
                    name: CategoryId::BILDUNG_UND_KARRIERE,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
            modifierIds: [
            ],
            modifierParameters: new ModifierParameters(
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 28
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 4
                ),
            ],
            conditionalResourceChanges: [
            ],
            zeitsteineDescription: '+1 Zeitstein für alle',
        );

        $konjunkturphase6 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(6),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom II – Mittlere Expansion',
            description: 'Ein globaler Handelsboom sorgt für Rekordgewinne bei den Unternehmen und spürbar steigende Löhne. Die Kaufkraft der Haushalte nimmt stark zu und zahlreiche Märkte expandieren. Da die Wirtschaft nun auf Hochtouren läuft und die Inflationsrisiken steigen, hebt die Zentralbank den Leitzins auf 3 % an. Die höheren Kreditkosten bremsen die Investitionen bislang jedoch kaum, da die Unternehmensgewinne weiterhin hoch sind.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 4),
                        new ZeitslotsPerPlayer(3, 5),
                        new ZeitslotsPerPlayer(4, 5),
                    ])
                ),
            ],
            modifierIds: [
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyKostenBildungUndKarrierePercent:105,
                modifyKostenSozialesUndFreizeitPercent:105,
                modifyLebenshaltungskostenMultiplier:105,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 10
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 34
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 5
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_JOB,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(0)),
                    lohnsonderzahlungPercent: 10,
                    description: 'Einmalige Lohnsonderzahlung i.H.v. 10 % des Erwerbseinkommens',
                ),
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase7 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(7),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom III – Überhitzung',
            description: 'Eine globale Rohstoffknappheit treibt die Preise weltweit in die Höhe. Unternehmen haben Mühe, die steigenden Kosten vollständig weiterzugeben, während sich zugleich erste Anzeichen einer Blasenbildung zeigen. Spekulationen haben dazu geführt, dass sich die Immobilienpreise zunehmend von den fundamentalen wirtschaftlichen Kennzahlen entkoppelt haben. Die Zentralbank reagiert mit einer deutlichen Anhebung des Leitzinses auf 4 %, um die Inflation zu bekämpfen. Die merklich gestiegenen Kreditkosten dämpfen bereits neue Investitionen.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 4),
                new ZeitsteinePerPlayer(3, 3),
                new ZeitsteinePerPlayer(4, 3),
            ]),
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
            modifierIds: [
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyKostenBildungUndKarrierePercent:110,
                modifyKostenSozialesUndFreizeitPercent:110,
                modifyLebenshaltungskostenMultiplier:110,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 7
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 30
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 12
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1000)),
                    isGrundsteuer: true,
                    description: 'Immobilienblase: für jede Immobilie fallen Steuern i.H.v. 1000 € pro Objekt an',
                ),
            ],
            zeitsteineDescription: '-1 Zeitstein für alle',
        );

        $konjunkturphase8 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(8),
            type: KonjunkturphaseTypeEnum::BOOM,
            name: 'Boom IV – Asset-Blase',
            description: 'Die langjährig niedrigen Zinsen haben spekulative Investitionen in Aktien, Immobilien und Kryptowährungen massiv begünstigt. Die Preise dieser Vermögenswerte sind stark überhöht und haben sich weit von ihren fundamentalen Werten entfernt. Die Zentralbank zieht nun deutlich die geldpolitische Bremse und hebt den Leitzins auf 5 % an, wodurch Kredite erheblich teurer werden. Experten warnen, dass die Wirtschaft vor einer Korrektur steht und eine Rezession droht, falls ein unerwarteter Schock eintritt.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 1),
                        new ZeitslotsPerPlayer(3, 2),
                        new ZeitslotsPerPlayer(4, 2),
                    ])
                ),
                new KompetenzbereichDefinition(
                    name: CategoryId::INVESTITIONEN,
                    zeitslots: new Zeitslots([
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
            modifierIds: [
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
                ModifierId::INCREASED_CHANCE_FOR_REZESSION,
            ],
            modifierParameters: new ModifierParameters(
                modifyKostenBildungUndKarrierePercent:115,
                modifyKostenSozialesUndFreizeitPercent:115,
                modifyLebenshaltungskostenMultiplier:115,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 12
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 40
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 2.1
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 10
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1000)),
                    isGrundsteuer: true,
                    description: 'Einmalige Grundsteuer pro Immobilie i.H.v. 1000 € pro Objekt',
                ),
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase9 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(9),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession I – Sanfte Abkühlung',
            description: 'Die Wirtschaft verliert leicht an Schwung, da internationale Handelskonflikte und erste Nachfragerückgänge Spuren hinterlassen. Unternehmen investieren vorsichtiger und verschieben größere Projekte. Die Zentralbank erkennt die schwache Entwicklung und senkt den Leitzins auf 1 %, wodurch Kredite günstig bleiben und ein stärkerer Abschwung verhindert werden soll. Der Staat reagiert mit einem Bildungsgutschein, um die Weiterbildung der Arbeitnehmer zu fördern.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
            modifierIds: [
            ],
            modifierParameters: new ModifierParameters(
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.4
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -2
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(bildungKompetenzsteinChange: 1),
                    description: 'Bildungs-Bonus: 1 Bildungs- & Karrierepunkt',
                ),
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase10 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(10),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession II – Nachfragerückgang',
            description: 'Ein stärkerer Rückgang der Nachfrage belastet zunehmend die Wirtschaft. Immer mehr Unternehmen müssen Kurzarbeit anmelden, wodurch Arbeitszeit und Einkommen sinken. Die Zentralbank hält den Leitzins auf dem niedrigen Niveau von 1 %, um weitere Schäden zu verhindern, doch die erhoffte Belebung bleibt vorerst aus. Die Unternehmen setzen aufgrund der schwierigen Lage Lohnsonderzahlungen aus, was den privaten Konsum zusätzlich belastet.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
                        new ZeitslotsPerPlayer(2, 2),
                        new ZeitslotsPerPlayer(3, 3),
                        new ZeitslotsPerPlayer(4, 3),
                    ])
                ),
            ],
            modifierIds: [
                ModifierId::GEHALT_CHANGE,
            ],
            modifierParameters: new ModifierParameters(
                modifyGehaltPercent:95,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 5.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -9
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: -6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.3
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -4
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_JOB,
                    resourceChanges: new ResourceChanges(zeitsteineChange: -1),
                    description: 'minus 1 Zeitstein wenn Erwerbseinkommen',
                ),
            ],
            zeitsteineDescription: '-1 Zeitstein, wenn Erwerbseinkommen vorhanden',
        );

        $konjunkturphase11 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(11),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession III – Nachfrageschwäche',
            description: 'Aufgrund eines anhaltenden Abschwungs bleibt die Stimmung in der Wirtschaft gedrückt. Unternehmen zeigen sich vorsichtig bei Neueinstellungen und Investitionen. Um die anhaltende Nachfrageschwäche abzumildern, senkt die Zentralbank den Leitzins auf 0,75 %, was zu sehr niedrigen Kreditkosten führt. Zusätzlich versucht die Regierung, die privaten Haushalte mit einem einmaligen Konjunkturbonus von 500 € pro Person zu unterstützen. Im Gegenzug wird für Immobilienbesitzer eine zusätzliche Grundsteuer erhoben.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
            modifierIds: [
            ],
            modifierParameters: new ModifierParameters(
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 5.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -7
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: -12
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.2
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -5
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(500)),
                    description: 'Konjunkturbonus i.H.v. 500 €',
                ),
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-500)),
                    isGrundsteuer: true,
                    description: 'Grundsteuer pro Immobilie i.H.v. 500 €',
                ),
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase12 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(12),
            type: KonjunkturphaseTypeEnum::REZESSION,
            name: 'Rezession IV – Kreditklemme',
            description: 'Banken werden aufgrund von Kreditausfällen zunehmend zurückhaltender. Unternehmen haben Schwierigkeiten, neue Kredite zu erhalten, wodurch viele Projekte vorerst aufgeschoben werden. Trotz einer Zinssenkung der Zentralbank auf 0,5 % bleibt der Kreditmarkt angespannt. Darlehensnehmer werden zusätzlich durch eine einmalige Zinszahlung belastet, während Immobilienwerte unter Druck geraten.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 4),
                new ZeitsteinePerPlayer(3, 3),
                new ZeitsteinePerPlayer(4, 3),
            ]),
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
                        new ZeitslotsPerPlayer(2, 3),
                        new ZeitslotsPerPlayer(3, 4),
                        new ZeitslotsPerPlayer(4, 4),
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
            modifierIds: [
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyKostenBildungUndKarrierePercent:95,
                modifyKostenSozialesUndFreizeitPercent:95,
                modifyLebenshaltungskostenMultiplier:95,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 6.5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -13
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: -20
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 1.1
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -7
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_LOAN,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-200)),
                    isExtraZins: true,
                    description: 'Einmaliger Extrazins für alle mit Darlehen i.H.v. 200 €',
                ),
            ],
            zeitsteineDescription: '-1 Zeitstein für alle',
        );

        $konjunkturphase13 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(13),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression I – Einsetzen der Deflation',
            description: 'Die Wirtschaftskrise verschärft sich deutlich. Unternehmen finden kaum noch Abnehmer für ihre Produkte und senken zunehmend ihre Preise, um Käufer anzulocken. Da immer weniger Menschen ihr Geld ausgeben, sinken die Preise weiter und es droht eine gefährliche Spirale. Die Zentralbank senkt den Leitzins nahezu auf null, doch die Zinssenkung zeigt kaum Wirkung. Die Verunsicherung am Markt lässt die Immobilienpreise sinken.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 4),
                new ZeitsteinePerPlayer(3, 3),
                new ZeitsteinePerPlayer(4, 3),
            ]),
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
            modifierIds: [
                ModifierId::GEHALT_CHANGE,
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyGehaltPercent:90,
                modifyKostenBildungUndKarrierePercent:95,
                modifyKostenSozialesUndFreizeitPercent:95,
                modifyLebenshaltungskostenMultiplier:95,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 7.25
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -16
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: -26
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 0.95
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -11
                ),
            ],
            conditionalResourceChanges: [
            ],
            zeitsteineDescription: '-1 Zeitstein für alle',
        );

        $konjunkturphase14 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(14),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression II – Bankenzusammenbruch',
            description: 'Eine Krise eskaliert, als mehrere große Banken plötzlich kurz vor der Insolvenz stehen. Um das gesamte Finanzsystem vor dem Kollaps zu retten, stützt die Regierung die Banken und setzt die Kreditvergabe vorübergehend aus. Daraus resultiert eine Panik an den Märkten. Immobilienpreise und Aktienkurse brechen ein. Dies geschieht trotz des radikalen Eingriffs der Zentralbank, die den Leitzins auf null senkt.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 4),
                new ZeitsteinePerPlayer(3, 3),
                new ZeitsteinePerPlayer(4, 3),
            ]),
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
            modifierIds: [
                ModifierId::GEHALT_CHANGE,
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
                ModifierId::KREDITSPERRE,
            ],
            modifierParameters: new ModifierParameters(
                modifyGehaltPercent:85,
                modifyKostenBildungUndKarrierePercent:90,
                modifyKostenSozialesUndFreizeitPercent:90,
                modifyLebenshaltungskostenMultiplier:90,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -26
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: -36
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 0.9
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -15
                ),
            ],
            conditionalResourceChanges: [
            ],
            zeitsteineDescription: '-1 Zeitstein für alle',
        );

        $konjunkturphase15 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(15),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression III – Stagnationstal',
            description: 'Die Wirtschaft scheint am Tiefpunkt einer Krise angekommen zu sein. Unternehmen zögern mit Investitionen und die Menschen sparen, statt ihr Geld auszugeben. Trotz massiver geldpolitischer Maßnahmen der Zentralbank und der Senkung des Leitzinses auf 0 % bleibt die Stimmung gedrückt. Um die Nachfrage kurzfristig anzukurbeln, verteilt der Staat eine einmalige Zahlung an alle Bürger.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 4),
                new ZeitsteinePerPlayer(3, 3),
                new ZeitsteinePerPlayer(4, 3),
            ]),
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
            modifierIds: [
                ModifierId::GEHALT_CHANGE,
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyGehaltPercent:85,
                modifyKostenBildungUndKarrierePercent:90,
                modifyKostenSozialesUndFreizeitPercent:90,
                modifyLebenshaltungskostenMultiplier:90,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 7
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: -3
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: -16
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 0.9
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: -3
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::NO_PREREQUISITES,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(500)),
                    description: 'Konjunkturbonus i.H.v. 500 € p.P.',
                ),
            ],
            zeitsteineDescription: '',
        );

        $konjunkturphase16 = new KonjunkturphaseDefinition(
            id: KonjunkturphasenId::create(16),
            type: KonjunkturphaseTypeEnum::DEPRESSION,
            name: 'Depression IV – Zäher Boden',
            description: 'Eine lange Krise hat tiefe Spuren hinterlassen. Viele Haushalte sind überschuldet und Unternehmen kämpfen weiterhin ums Überleben. Die Zentralbank hält den Leitzins bei null Prozent und sorgt dafür, dass Kredite günstig bleiben. Politik und Banken einigen sich auf eine teilweise Entschuldung, um die finanziellen Belastungen zu mildern. Infolge dieser Maßnahmen kehrt allmählich Vertrauen in die Wirtschaft zurück und die zuvor gefallenen Kurse beginnen sich zu stabilisieren.',
            additionalEvents: '',
            zeitsteine: new Zeitsteine([
                new ZeitsteinePerPlayer(2, 5),
                new ZeitsteinePerPlayer(3, 4),
                new ZeitsteinePerPlayer(4, 4),
            ]),
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
                        new ZeitslotsPerPlayer(2, 6),
                        new ZeitslotsPerPlayer(3, 7),
                        new ZeitslotsPerPlayer(4, 7),
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
            modifierIds: [
                ModifierId::GEHALT_CHANGE,
                ModifierId::BILDUNG_UND_KARRIERE_COST,
                ModifierId::SOZIALES_UND_FREIZEIT_COST,
                ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ],
            modifierParameters: new ModifierParameters(
                modifyGehaltPercent:90,
                modifyKostenBildungUndKarrierePercent:90,
                modifyKostenSozialesUndFreizeitPercent:90,
                modifyLebenshaltungskostenMultiplier:90,
            ),
            auswirkungen: [
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::LOANS_INTEREST_RATE,
                    value: 6
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::STOCKS_BONUS,
                    value: 5
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::CRYPTO,
                    value: 8
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::DIVIDEND,
                    value: 0.9
                ),
                new AuswirkungDefinition(
                    scope: AuswirkungScopeEnum::REAL_ESTATE,
                    value: 0
                ),
            ],
            conditionalResourceChanges: [
                new ConditionalResourceChange(
                    prerequisite: EreignisPrerequisitesId::HAS_LOAN,
                    resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-1000)),
                    isExtraZins: true,
                    description: 'Einmalig für offene Darlehen -1000 €',
                ),
            ],
            zeitsteineDescription: '',
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
     * @param bool $isChanceForRezessionIncreased
     * @return KonjunkturphaseDefinition
     */
    public static function getRandomKonjunkturphase(?KonjunkturphaseTypeEnum $lastType, bool $isChanceForRezessionIncreased = false): KonjunkturphaseDefinition
    {
        $possibleNextPhaseTypes = self::getListOfPossibleNextPhaseTypes($lastType, $isChanceForRezessionIncreased);

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
        return array_filter($allKonjunkturphasen, static fn (KonjunkturphaseDefinition $konjunkturphase) => in_array($konjunkturphase->type, $types, true));
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
     * Returns a list of possible next Konjunkturphasen types based on the current type. If the chance for a Rezession
     * is increased, there is a 50% chance this function will return an array that only contains
     * @see KonjunkturphaseTypeEnum::REZESSION
     *
     * @param KonjunkturphaseTypeEnum|null $konjunkturphaseType
     * @param bool $isChanceForRezessionIncreased
     * @return KonjunkturphaseTypeEnum[]
     * @internal
     */
    public static function getListOfPossibleNextPhaseTypes(
        ?KonjunkturphaseTypeEnum $konjunkturphaseType = null,
        bool $isChanceForRezessionIncreased = false
    ): array {
        $unmodifiedList = match ($konjunkturphaseType) {
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

        /**
         * Special case: If the chance for Rezession is increased, we will return only a Rezession in ~50% of the cases.
         * `mt_rand(0,1) === 1` has a ~50% chance to return true, in which case we will return an array containing just
         * Rezession. Otherwise we will return an array with all allowed Konjunkturphasen **without** Rezession.
         * WHY:
         * Rezession should be ~50% likely. So we have a 50% chance to only return Rezession and a 50% chance to return
         * all the other allowed KonjunkturphaseTypes
         * @see IncreasedChanceForRezessionModifier
         */
        if ($isChanceForRezessionIncreased) {
            $listWithoutRezession = array_filter(
                $unmodifiedList,
                fn ($konjunkturphaseType) => $konjunkturphaseType !== KonjunkturphaseTypeEnum::REZESSION
            );
            // @phpstan-ignore disallowed.function (we do not need cryptographical security, just a quick pseudorandom coin toss)
            return mt_rand(0, 1) === 1 ? [KonjunkturphaseTypeEnum::REZESSION] : $listWithoutRezession;
        }

        /**
         * Just return the unmodified list, if there is no special case.
         */
        return $unmodifiedList;
    }
}
