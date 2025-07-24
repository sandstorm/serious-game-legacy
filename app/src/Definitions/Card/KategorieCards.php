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
final class KategorieCards
{
    /**
     * @var array<PileID::value, KategorieCardDefinition[]> $cards
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
            PileId::BILDUNG_UND_KARRIERE_PHASE_1->value => [
                "buk0" => new KategorieCardDefinition(
                    id: new CardId('buk0'),
                    pileId: PileId::BILDUNG_UND_KARRIERE_PHASE_1,
                    title: 'Sprachkurs',
                    description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-11000),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),
                "buk1" => new KategorieCardDefinition(
                    id: new CardId('buk1'),
                    pileId: PileId::BILDUNG_UND_KARRIERE_PHASE_1,
                    title: 'Erste-Hilfe-Kurs',
                    description: 'Du machst einen Erste-Hilfe-Kurs, um im Notfall richtig zu reagieren.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-300),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),
                "buk2" => new KategorieCardDefinition(
                    id: new CardId('buk2'),
                    pileId: PileId::BILDUNG_UND_KARRIERE_PHASE_1,
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
                    pileId: PileId::BILDUNG_UND_KARRIERE_PHASE_1,
                    title: 'Irgendwas',
                    description: 'Mache jeden Tag 20 Minuten Gedächtnistraining, um dich geistig fit zu halten.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        bildungKompetenzsteinChange: +1,
                    ),
                ),

                "buk5" => new KategorieCardDefinition(
                    id: new CardId('buk5'),
                    pileId: PileId::BILDUNG_UND_KARRIERE_PHASE_1,
                    title: 'Ausbildung zur SkilehrerIn',
                    description: 'Erfülle dir deinen Traum und mache eine Ausbildung zur SkilehrerIn. Neben technischen Wissen eignest du dir geografische und pädagogische Kenntnisse an.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-7000),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),

                "buk6" => new KategorieCardDefinition(
                    id: new CardId('buk6'),
                    pileId: PileId::BILDUNG_UND_KARRIERE_PHASE_1,
                    title: 'Nachhilfe',
                    description: 'Nehme dir Nachhilfe, um deine Noten zu verbessern.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-600),
                        bildungKompetenzsteinChange: +1,
                    ),
                ),
            ],
            PileId::SOZIALES_UND_FREIZEIT_PHASE_1->value => [
                "suf0" => new KategorieCardDefinition(
                    id: new CardId('suf0'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'Ehrenamtliches Engagement',
                    description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-1200),
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),
                "suf1" => new KategorieCardDefinition(
                    id: new CardId('suf1'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'Spende',
                    description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-200),
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),
                "suf2" => new KategorieCardDefinition(
                    id: new CardId('suf2'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'kostenlose Nachhilfe',
                    description: 'Du gibst kostenlose Nachhilfe für sozial benachteiligte Kinder. Du verlierst einen Zeitstein.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),
                "suf3" => new KategorieCardDefinition(
                    id: new CardId('suf3'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'Ehrenamtliches Engagement',
                    description: 'Du engagierst dich wöchentlich in einem örtlichen Jugendzentrum. Dies kostet dich ein Zeitstein.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        freizeitKompetenzsteinChange: +1,
                    ),
                ),

                "suf4" => new KategorieCardDefinition(
                    id: new CardId('suf4'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'Sprachtandem',
                    description: 'Bilde ein Sprachtandem mit einem Erasmus-Studierenden und lerne viel über Sprache und fremde Kulturen.',
                    resourceChanges: new ResourceChanges(
                        zeitsteineChange: -1,
                        freizeitKompetenzsteinChange: +1,
                        //TODO: Vorraussetzung Eintritt von Ereigniskarte: mit Ereigniskarte Sprachtandem verknüpfen
                    ),
                ),

                "suf10" => new KategorieCardDefinition(
                    id: new CardId('suf10'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'Sozialhilfe',
                    description: 'Engagiere eine Sozialhilfe zur Pflege deiner Großeltern.',
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(-30.000),
                        freizeitKompetenzsteinChange: +2,
                        //TODO: Folgen: In der nächsten Runde darfst du zwei Zeitsteine auf einmal setzten
                    ),
                ),

                "suf14" => new KategorieCardDefinition(
                    id: new CardId('suf14'),
                    pileId: PileId::SOZIALES_UND_FREIZEIT_PHASE_1,
                    title: 'SteuerberaterIn',
                    description: 'Dir wachsen deine Unterlagen vom letzten Jahr langsam über den Kopf. Engagiere eine:n Steuerberater:in.',
                    resourceChanges: new ResourceChanges(
                        //TODO: (-) 10% deines Gehalts oder min. 2000 €
                        freizeitKompetenzsteinChange: +1,
                    ),
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
            PileId::BILDUNG_UND_KARRIERE_PHASE_1 => $this->getCardsForBildungAndKarriere1(),
            PileId::SOZIALES_UND_FREIZEIT_PHASE_1 => $this->getCardsForSozialesAndFreizeit1(),
            PileId::JOBS_PHASE_1 => $this->getCardsForJobs1(),
            PileId::MINIJOBS => $this->getCardsForMinijobs1(),
            PileId::BILDUNG_UND_KARRIERE_PHASE_1_EREIGNISSE => $this->getCardsForEreignisseBildungUndKarriere1(),
            // TODO
            PileId::BILDUNG_UND_KARRIERE_PHASE_2 => [],
            PileId::SOZIALES_UND_FREIZEIT_PHASE_2 => [],
            PileId::JOBS_PHASE_2 => [],
            PileId::BILDUNG_UND_KARRIERE_PHASE_3 => [],
            PileId::SOZIALES_UND_FREIZEIT_PHASE_3 => [],
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
        $result = $this->cards[PileId::BILDUNG_UND_KARRIERE_PHASE_1->value];
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
        $result = $this->cards[PileId::SOZIALES_UND_FREIZEIT_PHASE_1->value];
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
        $result = $this->cards[PileId::MINIJOBS->value];
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
        $result = $this->cards[PileId::BILDUNG_UND_KARRIERE_PHASE_1_EREIGNISSE->value];
        foreach ($result as $item) {
            assert($item instanceof EreignisCardDefinition);
        }
        return $result;
    }
}
