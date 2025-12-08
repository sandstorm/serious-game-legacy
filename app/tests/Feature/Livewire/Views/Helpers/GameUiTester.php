<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

readonly class GameUiTester {

    public Testable $testableGameUi;

    public function __construct(GameId $gameId, private PlayerId $playerId, private string $playerName) {
        $this->testableGameUi = Livewire::test(GameUi::class, [
            'gameId' => $gameId,
            'myself' => $this->playerId
        ]);
    }

    public function startGame(TestCase $testCase): static {

        $this->testableGameUi
            ->assertSee([
                'Eine neue Konjunkturphase beginnt.',
                'Das nächste Szenario ist:',
                $testCase->getKonjunkturphaseDefinition()->type->value
            ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->assertSee([
                $testCase->getKonjunkturphaseDefinition()->type->value,
                $testCase->getKonjunkturphaseDefinition()->description
            ])
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee([
                'Bildung & Karriere',
                'Freizeit & Soziales',
                'Beruf',
                'Finanzen',
                'Konjunktur',
                $testCase->getKonjunkturphaseDefinition()->type->value,
                'Dein Lebensziel',
                'Ereignisprotokoll:',
                'Eine neue Konjunkturphase \'' . $testCase->getKonjunkturphaseDefinition()->name . '\' beginnt.',
                $testCase->getCardsForBildungAndKarriere()['buk0']->getTitle(),
                $testCase->getCardsForSozialesAndFreizeit()['suf0']->getTitle(),
                'Jobbörse',
                'Investitionen',
                'Weiterbildung',
                'Minijob',
                'Kredit aufnehmen',
                'Versicherung abschließen',
                'Spielzug beenden'
            ]);

        return $this;
    }

    public function drawAndPlayCard(TestCase $testCase, CategoryId $categoryId) {
        dump('test');

        // get top card from category
        $playerState = PlayerState::getCurrentLebenszielphaseIdForPlayer($testCase->getGameEvents(), $this->playerId);
        $pileId = new PileId($categoryId, $playerState);
        dump('pile id: ', $pileId);
        $topCardIdForPile = PileState::topCardIdForPile($testCase->getGameEvents(), $pileId);
        dump('id top card: ', $topCardIdForPile->value);
        $topCard = CardFinder::getInstance()->getCardById(new CardId($topCardIdForPile->value), KategorieCardDefinition::class);
        dump('top card: ', $topCard);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;
        $topCardZeitstein = $topCard->getResourceChanges()->zeitsteineChange;
//        dump('top card zeitstein', $topCardZeitstein);

        /*
        dump('title: ', $topCardTitle);
        dump('description: ', $topCard->getDescription());
//        dump('resourcen: ',$topCard->getResourceChanges());
        dump(
            'guthaben: ',
            $topCardGuthabenChange,
            number_format(abs($topCardGuthabenChange), 2, ',', '.')
        );
        dump('fus: ', $topCard->getResourceChanges()->freizeitKompetenzsteinChange);
        dump('buk: ', $topCard->getResourceChanges()->bildungKompetenzsteinChange);
        dump('category: ', $topCard->getCategory()->value);
        dump($this->playerName);
        */

        $zeitsteineInCategoriesBeforeAction = $this->zeitsteineInCategories($testCase, $categoryId);

        dump('...........................................');

        // check that player has all of his Zeitsteine
        $zeitsteineBeforeAction = $this->remainingZeitsteine($testCase);
        dump($zeitsteineBeforeAction);

        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCardIdForPile->value, $topCard->getCategory()->value)
            ->assertSee([
                $topCardTitle,
                $topCard->getDescription(),
                $topCardGuthabenChange === 0 ? '' : number_format(abs($topCardGuthabenChange), 2, ',', '.'),
                'Karte spielen'
            ])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte '$topCardTitle'")
            // play card
            ->call('activateCard', $topCard->getCategory()->value)
            // check that message is now logged
            ->assertSee("spielt Karte '$topCardTitle'");

        // ToDo
        // check that 1 Zeitstein is used for responding category
//            ->assertSeeHtml('1 von 3 Zeitsteinen wurden platziert. Player 0: 1, Player 1: 0')
        // check that player got 1 Kompetenzstein for responding category
//            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 1 von 1');

        // check that player has used 1 Zeitstein
        $zeitsteineAfterAction = $this->remainingZeitsteine($testCase);
        dump($zeitsteineAfterAction);

        dump('...........................................');
        Assert::assertEquals($zeitsteineBeforeAction - 1 + $topCardZeitstein, $zeitsteineAfterAction, 'Zeitsteine um 1 reduziert');

        // ----------------------------------------------------------------------------------
        $zeitsteineInCategoriesAfterAction = $this->zeitsteineInCategories($testCase, $categoryId);



        array_map([$this,'compareUsedSlots'], $zeitsteineInCategoriesAfterAction, $zeitsteineInCategoriesBeforeAction);


        return $this;

    }

    private function compareUsedSlots($amountZeitsteineAfter, $amountZeitsteineBefore): void {
        dump('compare: ',$amountZeitsteineAfter['amount'], $amountZeitsteineBefore['amount']);
        return;
    }



    private function zeitsteineInCategories(TestCase $testCase, CategoryId $categoryId): array {

        $categories = [
            CategoryId::BILDUNG_UND_KARRIERE->name => ['categoryId' => CategoryId::BILDUNG_UND_KARRIERE],
            CategoryId::SOZIALES_UND_FREIZEIT->name => ['categoryId' => CategoryId::SOZIALES_UND_FREIZEIT],
            CategoryId::JOBS->name => ['categoryId' => CategoryId::JOBS],
            CategoryId::INVESTITIONEN->name => ['categoryId' => CategoryId::INVESTITIONEN]
        ];

        $players = $testCase->players;

        foreach ($categories as $name => $category) {
            $categoryId = $category['categoryId'];
            $availableSlots = $testCase
                ->getKonjunkturphaseDefinition()
                ->getKompetenzbereichByCategory($categoryId)
                ->zeitslots
                ->getAmountOfZeitslotsForPlayerCount(2);


            $zeitsteinePerPlayer = [];
            $usedSlots = 0;
            foreach ($players as $player) {
                $playerName = PlayerState::getNameForPlayer($testCase->getGameEvents(), $player);
                $placedZeitsteineByPlayer = PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $player, $categoryId);
                $usedSlots += $placedZeitsteineByPlayer;
                $zeitsteinePerPlayer[] = $playerName . ': ' . $placedZeitsteineByPlayer;
            }

            $categories[$name]['amount'] = $usedSlots;

            dump($usedSlots . ' von ' . $availableSlots . ' Zeitsteinen wurden platziert. ' . implode(', ', $zeitsteinePerPlayer));


        }
        dump($categories);
        return $categories;
    }

    private function remainingZeitsteine(TestCase $testCase): int {
        $totalAmountOfZeitsteineForPlayer = $testCase
            ->getKonjunkturphaseDefinition()
            ->zeitsteine
            ->getAmountOfZeitsteineForPlayer(2);
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        dump($playerResources);
        $currentZeitsteine = $playerResources->zeitsteineChange;
        $this->testableGameUi->assertSeeHtml(
            $this->playerName . ' hat noch ' . $currentZeitsteine . ' von ' . $totalAmountOfZeitsteineForPlayer . ' Zeitsteinen übrig.'
        );

        return $currentZeitsteine;
    }

    public function finishTurn() {
        $this->testableGameUi->call('spielzugAbschliessen');
    }

    private function zeitsteineInCategories2(TestCase $testCase, CategoryId $categoryId) {
        $konjunkturphaseDefinition = $testCase->getKonjunkturphaseDefinition();
        $availableSlots = $testCase
            ->getKonjunkturphaseDefinition()
            ->getKompetenzbereichByCategory($categoryId)
            ->zeitslots
            ->getAmountOfZeitslotsForPlayerCount(2);

        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $fusKompetenz = $playerResources->freizeitKompetenzsteinChange;
        $busKompetenz = $playerResources->bildungKompetenzsteinChange;

//        $players = $testCase->players;
        dump('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
//        $players = GamePhaseState::getOrderedPlayers($testCase->getGameEvents());
        $players = [];
        foreach ($testCase->players as $player) {
//            $players['id'] = $player->value;
//            dump($player->value);
//            $test=['name'=>'abc'];
            $players[$player->value] = [
                'name' => PlayerState::getNameForPlayer($testCase->getGameEvents(), $player),
                CategoryId::BILDUNG_UND_KARRIERE->value => PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $player, CategoryId::BILDUNG_UND_KARRIERE),
                CategoryId::BILDUNG_UND_KARRIERE->name => PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $player, CategoryId::BILDUNG_UND_KARRIERE),
                CategoryId::SOZIALES_UND_FREIZEIT->name => PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $player, CategoryId::SOZIALES_UND_FREIZEIT)
            ];
        }
        dump('players: ', $players, $testCase->players);
//        dump($players);
        dump('bla',
//            GameboardInformationForCategory::class->getZeitsteineForCategory(CategoryId::BILDUNG_UND_KARRIERE)
//            Categories::class,
            'name: ' . PlayerState::getNameForPlayer($testCase->getGameEvents(), $this->playerId),
            'zeitsteine, die Player übrig hat: ' . PlayerState::getZeitsteineForPlayer($testCase->getGameEvents(), $this->playerId),
            'zeitsteine in buk platziert: ' . PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $this->playerId, CategoryId::BILDUNG_UND_KARRIERE),
            'zeitsteine in fus platziert: ' . PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $this->playerId, CategoryId::SOZIALES_UND_FREIZEIT),
            'bla'
        );

        dump('Kompetenzbereich by category', $testCase->getKonjunkturphaseDefinition()->getKompetenzbereichByCategory($categoryId));

        dump('category id: ', $categoryId, 'total amount of Zeitslots: ', $availableSlots);
        dump('player resources: ', $playerResources);
        dump($fusKompetenz, $busKompetenz);

        $this->testableGameUi->assertSeeHtml(" von $availableSlots Zeitsteinen wurden platziert.");

    }

}

