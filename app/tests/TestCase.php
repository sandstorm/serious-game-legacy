<?php

namespace Tests;

use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectPlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\JobRequirements;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\Gehalt;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ForCoreGameLogic $coreGameLogic;
    protected GameId $gameId;
    protected PileId $pileIdBildung;
    /**
     * @var PlayerId[]
     */
    protected array $players;
    /**
     * @var CardDefinition[]
     */
    protected array $cardsBildung;
    protected PileId $pileIdFreizeit;
    /**
     * @var CardDefinition[]
     */
    protected array $cardsFreizeit;
    protected PileId $pileIdJobs;
    /**
     * @var CardDefinition[]
     */
    protected array $cardsJobs;

    private function generatePlayerIds(int $numberOfPlayers) {
        assert (2 <= $numberOfPlayers && $numberOfPlayers <=4, "Only 2-4 players are supported");
        $playerIds = [];
        for ($i = 0; $i < $numberOfPlayers; $i++) {
            $playerIds[$i] = PlayerId::fromString('p'. $i+1);
        }
        return $playerIds;
    }

    public function setupBasicGame(int $numberOfPlayers = 2): void
    {
        $this->coreGameLogic = CoreGameLogicApp::createInMemoryForTesting();
        $this->gameId = GameId::fromString('game1');
        $this->players = $this->generatePlayerIds($numberOfPlayers);
        CardFinder::getInstance()->overrideCardsForTesting([
            PileId::BILDUNG_PHASE_1->value => $this->getCardsForBildungAndKarriere(),
            PileId::FREIZEIT_PHASE_1->value => $this->getCardsForSozialesAndFreizeit(),
            PileId::JOBS_PHASE_1->value => $this->getCardsForJobs(),
        ]);

        $this->pileIdBildung = PileId::BILDUNG_PHASE_1;
        $this->cardsBildung = $this->getCardsForBildungAndKarriere();
        $this->pileIdFreizeit = PileId::FREIZEIT_PHASE_1;
        $this->cardsFreizeit = $this->getCardsForSozialesAndFreizeit();
        $this->pileIdJobs = PileId::JOBS_PHASE_1;
        $this->cardsJobs = $this->getCardsForJobs();

        $this->coreGameLogic->handle($this->gameId, StartPreGame::create(
            numberOfPlayers: $numberOfPlayers,
        )->withFixedPlayerIdsForTesting(...$this->players));
        foreach ($this->players as $index => $player) {
            $this->coreGameLogic->handle($this->gameId, new SetNameForPlayer(
                playerId: $player,
                name: 'Player ' . $index,
            ));
            $this->coreGameLogic->handle($this->gameId, new SelectLebensziel(
                playerId: $player,
                lebensziel: LebenszielId::create($index % 2 + 1),
            ));
            $this->coreGameLogic->handle($this->gameId, new SelectPlayerColor(
                playerId: $player,
                playerColor: null,
            ));
        }

        $this->coreGameLogic->handle($this->gameId, StartGame::create());

        $this->coreGameLogic->handle(
            $this->gameId,
            ChangeKonjunkturphase::create()->withFixedCardOrderForTesting(
                new CardOrder( pileId: $this->pileIdBildung, cards: array_map(fn ($card) => $card->id, $this->cardsBildung)),
                new CardOrder( pileId: $this->pileIdFreizeit, cards: array_map(fn ($card) => $card->id, $this->cardsFreizeit)),
                new CardOrder( pileId: $this->pileIdJobs, cards: array_map(fn ($card) => $card->id, $this->cardsJobs)),
            ));
    }

    /**
     * @return JobCardDefinition[]
     */
    protected function getCardsForJobs(): array
    {
        return [
            "j0" => new JobCardDefinition(
                id: new CardId('j0'),
                pileId: PileId::JOBS_PHASE_1,
                title: 'Fachinformatikerin',
                description: 'Du hast nun wegen deines Jobs weniger Zeit und kannst pro Jahr einen Zeitstein weniger setzen.',
                gehalt: new Gehalt(34000),
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
                gehalt: new Gehalt(25000),
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
                gehalt: new Gehalt(18000),
                requirements: new JobRequirements(
                    zeitsteine: 1,
                    bildungKompetenzsteine: 1,
                ),
            ),
        ];
    }

    /**
     * @return KategorieCardDefinition[]
     */
    protected function getCardsForSozialesAndFreizeit(): array
    {
        return [
            "suf0" => new KategorieCardDefinition(
                id: new CardId('suf0'),
                pileId: PileId::FREIZEIT_PHASE_1,
                title: 'Ehrenamtliches Engagement',
                description: 'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -1200,
                    freizeitKompetenzsteinChange: +1,
                ),
            ),
            "suf1" => new KategorieCardDefinition(
                id: new CardId('suf1'),
                pileId: PileId::FREIZEIT_PHASE_1,
                title: 'Spende',
                description: 'Bei deinem Einkauf spendest du nun immer Tiernahrung für die umliegende Tierheime. Dein Spendebeitrag ist 200 €.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -200,
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
        ];
    }

    /**
     * @return KategorieCardDefinition[]
     */
    private function getCardsForBildungAndKarriere(): array
    {
        return [
            "buk0" => new KategorieCardDefinition(
                id: new CardId('buk0'),
                pileId: PileId::BILDUNG_PHASE_1,
                title: 'Sprachkurs',
                description: 'Mache einen Sprachkurs über drei Monate im Ausland.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -11000,
                    bildungKompetenzsteinChange: +1,
                ),
            ),
            "buk1" => new KategorieCardDefinition(
                id: new CardId('buk1'),
                pileId: PileId::BILDUNG_PHASE_1,
                title: 'Erste-Hilfe-Kurs',
                description: 'Du machst einen Erste-Hilfe-Kurs, um im Notfall richtig zu reagieren.',
                resourceChanges: new ResourceChanges(
                    guthabenChange: -300,
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
        ];
    }

    /**
     * @param array<PileID::value, CardDefinition[]> $cards
     * @return void
     */
    protected function setCards(array $cards): void
    {

    }


    /**
     * @return CardDefinition[]
     */
    protected function getCardsForTesting(): array
    {
        return [
            ...$this->getCardsForSozialesAndFreizeit(),
            ...$this->getCardsForBildungAndKarriere(),
            ...$this->getCardsForJobs()
        ];
    }
}
