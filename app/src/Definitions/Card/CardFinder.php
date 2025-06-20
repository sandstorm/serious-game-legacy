<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
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
                )
            ],
            PileId::JOBS_PHASE_1->value => [
                "ee0" => new JobCardDefinition(
                    id: new CardId('ee0'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Fachinformatikerin',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(34000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "ee1" => new JobCardDefinition(
                    id: new CardId('ee1'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Pflegefachkraft',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(25000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 2,
                    ),
                ),
                "ee2" => new JobCardDefinition(
                    id: new CardId('ee2'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Taxifahrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(18000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 1,
                    ),
                ),
                "ee3" => new JobCardDefinition(
                    id: new CardId('ee3'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Geschichtslehrer:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(40000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 0,
                    ),
                ),
                "ee4" => new JobCardDefinition(
                    id: new CardId('ee4'),
                    pileId: PileId::JOBS_PHASE_1,
                    title: 'Bruchpilot:in',
                    description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                    gehalt: new MoneyAmount(4000),
                    requirements: new JobRequirements(
                        zeitsteine: 1,
                        bildungKompetenzsteine: 0,
                    ),
                ),
            ]
        ]);
        return self::$instance;
    }

    /**
     * @param array<PileID::value, CardDefinition[]> $cards
     * @return void
     */
    public function overrideCardsForTesting(array $cards): void
    {
        self::getInstance()->cards = $cards;
    }

    /**
     * @param array<PileID::value, CardDefinition[]> $cards
     */
    private function __construct(array $cards)
    {
        $this->cards = $cards;
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
            // TODO
            PileId::BILDUNG_PHASE_2 => [],
            PileId::FREIZEIT_PHASE_2 => [],
            PileId::JOBS_PHASE_2 => [],
            PileId::BILDUNG_PHASE_3 => [],
            PileId::FREIZEIT_PHASE_3 => [],
            PileId::JOBS_PHASE_3 => [],
        };
    }

    public function getCardById(CardId $cardId): CardDefinition
    {
        $allCards = array_reduce($this->cards, function ($cards, $currentPile) {
            return [...$cards, ...$currentPile];
        }, []);
        if (array_key_exists($cardId->value, $allCards)) {
            return $allCards[$cardId->value];
        }

        throw new \RuntimeException('Card ' . $cardId . ' does not exist', 1747645954);
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

}
