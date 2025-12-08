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
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
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
        // get top card from category
        $playerState = PlayerState::getCurrentLebenszielphaseIdForPlayer($testCase->getGameEvents(), $this->playerId);
        $pileId = new PileId($categoryId, $playerState);
        $topCardIdForPile = PileState::topCardIdForPile($testCase->getGameEvents(), $pileId);
        $topCard = CardFinder::getInstance()->getCardById(new CardId($topCardIdForPile->value), KategorieCardDefinition::class);

        /*
        dump('pile id: ', $pileId);
        dump('id top card: ', $topCardIdForPile->value);
        dump('top card: ', $topCard);
        */

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;
        $topCardZeitstein = $topCard->getResourceChanges()->zeitsteineChange;

        dump('ressourcen: ', $topCard->getResourceChanges());

        /*
        dump('title: ', $topCardTitle);
        dump('description: ', $topCard->getDescription());
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

        // check that players amount of Zeitsteine is unchanged
        $zeitsteineBeforeAction = $this->remainingZeitsteine($testCase);

        // check that Zeitsteine in categories are unchanged
        $categoryInfoBeforeAction = $this->getCategoryInfo($testCase);

        // ToDo
        // check that Kompetenzsteine are unchanged
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine($testCase);

        // draw and play card
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

        // check that player has used Zeitsteine
        $zeitsteineAfterAction = $this->remainingZeitsteine($testCase);
        Assert::assertEquals($zeitsteineBeforeAction - 1 + $topCardZeitstein, $zeitsteineAfterAction, 'Zeitsteine um 1 reduziert');

        // check that 1 Zeitstein is used for corresponding category
        $categoryInfoAfterAction = $this->getCategoryInfo($testCase);
        foreach ($categoryInfoAfterAction as $name => $category) {
            $categoryInfoAfterAction[$name]['playedThisTurn'] = $category['categoryId'] === $categoryId;
        }
        array_map([$this, 'compareUsedSlots'], $categoryInfoAfterAction, $categoryInfoBeforeAction);

        // ToDo
        // check that player got Kompetenzstein for corresponding category
//            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 1 von 1');
        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine($testCase);
        foreach ($playersKompetenzsteineAfterAction as $category => $kompetenzstein) {
            $playersKompetenzsteineAfterAction[$category]['changedThisTurn'] = $category === $categoryId->name;
        }

        array_map([$this, 'compareKompetenzsteine'], $playersKompetenzsteineAfterAction, $playersKompetenzsteineBeforeAction);

        // ToDo
        // check that player paid corresponding

        return $this;

    }

    private function remainingZeitsteine(TestCase $testCase): int {
        $totalAmountOfZeitsteineForPlayer = $testCase
            ->getKonjunkturphaseDefinition()
            ->zeitsteine
            ->getAmountOfZeitsteineForPlayer(count($testCase->players));
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $currentZeitsteine = $playerResources->zeitsteineChange;
        $this->testableGameUi->assertSeeHtml(
            $this->playerName . ' hat noch ' . $currentZeitsteine . ' von ' . $totalAmountOfZeitsteineForPlayer . ' Zeitsteinen übrig.'
        );

        return $currentZeitsteine;
    }

    private function getCategoryInfo(TestCase $testCase): array {
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
                ->getAmountOfZeitslotsForPlayerCount(count($players));

            $zeitsteinePerPlayer = [];
            $usedSlots = 0;
            foreach ($players as $player) {
                $playerName = PlayerState::getNameForPlayer($testCase->getGameEvents(), $player);
                $placedZeitsteineByPlayer = PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $player, $categoryId);
                $usedSlots += $placedZeitsteineByPlayer;
                $zeitsteinePerPlayer[] = $playerName . ': ' . $placedZeitsteineByPlayer;
            }

            $categories[$name]['usedSlots'] = $usedSlots;

            $this->testableGameUi->assertSeeHtml($usedSlots . ' von ' . $availableSlots . ' Zeitsteinen wurden platziert. ' . implode(', ', $zeitsteinePerPlayer));
        }

        return $categories;
    }

    private function getPlayersKompetenzsteine($testCase): array {
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $categories = [
            CategoryId::BILDUNG_UND_KARRIERE->name => [
                'amount' => $playerResources->bildungKompetenzsteinChange,
                'categoryId' => CategoryId::BILDUNG_UND_KARRIERE
            ],
            CategoryId::SOZIALES_UND_FREIZEIT->name => [
                'amount' => $playerResources->freizeitKompetenzsteinChange,
                'categoryId' => CategoryId::SOZIALES_UND_FREIZEIT
            ]];

        foreach ($categories as $category) {
            $categoryNameArray = explode(' & ', $category['categoryId']->value);
            $achievedAmount = $category['amount'];
            $availableAmount = 1;
            dump();
            dump("Deine Kompetenzsteine im Bereich $categoryNameArray[0] &amp; $categoryNameArray[1]: $achievedAmount von $availableAmount");
            // ToDo: max vorhandene Slots für Kompetenzstein ermitteln (von 1, etc.)
//            $this->testableGameUi->assertSeeHtml("Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 1 von 1");
            $this->testableGameUi->assertSeeHtml("Deine Kompetenzsteine im Bereich $categoryNameArray[0] &amp; $categoryNameArray[1]");
        }

        return $categories;

    }

    public function startTurn(): static {
        // check that modal is visible
        $this->testableGameUi->assertSee('Du bist am Zug');
        $this->testableGameUi->assertSeeHtml('startSpielzug');

        // player confirms starting turn
        $this->testableGameUi->call('startSpielzug');

        // check that modal is not visible anymore
        $this->testableGameUi->assertDontSee('Du bist am Zug');
        $this->testableGameUi->assertDontSeeHtml('startSpielzug');

        return $this;
    }

    public function finishTurn() {
        $this->testableGameUi->call('spielzugAbschliessen');
    }

    private function compareKompetenzsteine($kompetenzsteinAfterAction, $kompetenzsteinBeforeAction): void {
        $category = $kompetenzsteinAfterAction['categoryId']->name;
        if ($kompetenzsteinAfterAction['changedThisTurn']) {
            // ToDo: Änderung (-1, -2, etc.) ermitteln
            Assert::assertEquals($kompetenzsteinAfterAction['amount'] - 1, $kompetenzsteinBeforeAction['amount'], "Kompetenzsteine in $category um 1 erhöht");
        } else {
            Assert::assertEquals($kompetenzsteinAfterAction['amount'], $kompetenzsteinBeforeAction['amount'], "Kompetenzsteine in $category sind gleich geblieben");
        }
    }

    private function compareUsedSlots($categoryInfoAfterAction, $categoryInfoBeforeAction): void {
        $category = $categoryInfoAfterAction['categoryId']->name;
        if ($categoryInfoAfterAction['playedThisTurn']) {
            Assert::assertEquals($categoryInfoAfterAction['usedSlots'] - 1, $categoryInfoBeforeAction['usedSlots'], "Zeitsteine in $category um 1 erhöht");
        } else {
            Assert::assertEquals($categoryInfoAfterAction['usedSlots'], $categoryInfoBeforeAction['usedSlots'], "Zeitsteine in $category sind gleich geblieben");
        }
    }

}

