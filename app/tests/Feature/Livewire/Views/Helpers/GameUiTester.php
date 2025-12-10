<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
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
            ->call('startKonjunkturphaseForPlayer');

        $this->seeUpdatedGameboard($testCase);

        return $this;
    }

    private function seeUpdatedGameboard(TestCase $testCase): void {
        $this->testableGameUi
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
                $this->getTopCardFromCategory($testCase, CategoryId::BILDUNG_UND_KARRIERE)->getTitle(),
                $this->getTopCardFromCategory($testCase, CategoryId::SOZIALES_UND_FREIZEIT)->getTitle(),
                'Jobbörse',
                'Investitionen',
                'Weiterbildung',
                'Minijob',
                'Kredit aufnehmen',
                'Versicherung abschließen',
                'Spielzug beenden'
            ]);
    }

    /**
     * @param TestCase $testCase
     * @param CategoryId $categoryId
     * @return KategorieCardDefinition | JobCardDefinition
     */
    private function getTopCardFromCategory(TestCase $testCase, CategoryId $categoryId): KategorieCardDefinition|JobCardDefinition {
        $playerState = PlayerState::getCurrentLebenszielphaseIdForPlayer($testCase->getGameEvents(), $this->playerId);
        $pileId = new PileId($categoryId, $playerState);
        $topCardIdForPile = PileState::topCardIdForPile($testCase->getGameEvents(), $pileId);
        return CardFinder::getInstance()->getCardById(new CardId($topCardIdForPile->value));
    }

    public function drawAndPlayCard(TestCase $testCase, CategoryId $categoryId) {
        // get top card from category
        $topCard = $this->getTopCardFromCategory($testCase, $categoryId);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;
        $topCardZeitstein = $topCard->getResourceChanges()->zeitsteineChange;

        // check that players amount of Zeitsteine is unchanged
        $playersZeitsteineBeforeAction = $this->remainingZeitsteine($testCase);

        // check that Zeitsteine in categories are unchanged
        $categoryInfoBeforeAction = $this->getCategoryInfo($testCase);

        // check that Kompetenzsteine are unchanged
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine($testCase);

        // check that players balance remains the same
        $playersBalanceBeforeAction = $this->getPlayersBalance($testCase);

        // draw and play card
        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCard->getId()->value, $categoryId->value)
            ->assertSee([
                $topCardTitle,
                $topCard->getDescription(),
                $topCardGuthabenChange === 0 ? '' : number_format(abs($topCardGuthabenChange), 2, ',', '.'),
                'Karte spielen'
            ])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte '$topCardTitle'")
            // play card
            ->call('activateCard', $categoryId->value)
            // check that message is now logged
            ->assertSee("spielt Karte '$topCardTitle'");

        // check that player has used Zeitsteine
        $playersZeitsteineAfterAction = $this->remainingZeitsteine($testCase);
        Assert::assertEquals($playersZeitsteineBeforeAction - 1 + $topCardZeitstein, $playersZeitsteineAfterAction, 'Zeitsteine have been reduced');

        // check that 1 Zeitstein is used for corresponding category
        $categoryInfoAfterAction = $this->getCategoryInfo($testCase);
        foreach ($categoryInfoAfterAction as $name => $category) {
            $categoryInfoAfterAction[$name]['playedThisTurn'] = $category['categoryId'] === $categoryId;
        }
        array_map([$this, 'compareUsedSlots'], $categoryInfoAfterAction, $categoryInfoBeforeAction);

        // check that player got Kompetenzstein for corresponding category
        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine($testCase);
        foreach ($playersKompetenzsteineAfterAction as $category => $kompetenzstein) {
            $playersKompetenzsteineAfterAction[$category]['changedThisTurn'] = $category === $categoryId->name;
            $playersKompetenzsteineAfterAction[$category]['kompetenzsteinChange'] = $category === CategoryId::BILDUNG_UND_KARRIERE->name ? $topCard->getResourceChanges()->bildungKompetenzsteinChange : $topCard->getResourceChanges()->freizeitKompetenzsteinChange;
        }
        array_map([$this, 'compareKompetenzsteine'], $playersKompetenzsteineAfterAction, $playersKompetenzsteineBeforeAction);

        // check that player has paid
        $playersBalanceAfterAction = $this->getPlayersBalance($testCase);
        Assert::assertEquals($playersBalanceBeforeAction + $topCardGuthabenChange, $playersBalanceAfterAction, "Balance has been reduced by $topCardGuthabenChange");

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

    private function getPlayersKompetenzsteine(TestCase $testCase): array {
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $currentLebenszielphaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer(
            $testCase->getGameEvents(),
            $this->playerId
        );
        $categories = [
            CategoryId::BILDUNG_UND_KARRIERE->name => [
                'amount' => $playerResources->bildungKompetenzsteinChange,
                'availableSlots' => $currentLebenszielphaseDefinition->bildungsKompetenzSlots,
                'categoryId' => CategoryId::BILDUNG_UND_KARRIERE
            ],
            CategoryId::SOZIALES_UND_FREIZEIT->name => [
                'amount' => $playerResources->freizeitKompetenzsteinChange,
                'availableSlots' => $currentLebenszielphaseDefinition->freizeitKompetenzSlots,
                'categoryId' => CategoryId::SOZIALES_UND_FREIZEIT
            ]];

        foreach ($categories as $category) {
            $categoryNameArray = explode(' & ', $category['categoryId']->value);
            $achievedAmount = $category['amount'];
            $availableAmount = $category['availableSlots'];

            $this->testableGameUi->assertSeeHtml(
                "Deine Kompetenzsteine im Bereich $categoryNameArray[0] &amp; $categoryNameArray[1]: $achievedAmount von $availableAmount"
            );
        }

        return $categories;
    }

    private function getPlayersBalance(TestCase $testCase): float {
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $balance = $playerResources->guthabenChange->value;
        $this->testableGameUi->assertSee(number_format($balance, 2, ',', '.'));

        return $balance;
    }

    public function tryToPlayCardOnWrongTurn(TestCase $testCase, CategoryId $categoryId): void {
        // get top card from category
        $topCard = $this->getTopCardFromCategory($testCase, $categoryId);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;

        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCard->getId()->value, $categoryId->value)
            ->assertSee([
                $topCardTitle,
                $topCard->getDescription(),
                $topCardGuthabenChange === 0 ? '' : number_format(abs($topCardGuthabenChange), 2, ',', '.'),
                'Karte spielen'
            ])
            // play card
            ->call('activateCard', $categoryId->value)
            // show error message
            ->assertSee('Du bist gerade nicht dran');
    }

    public function startTurn(TestCase $testCase): static {
        $this->testableGameUi
            // check that modal is visible
            ->assertSee('Du bist am Zug')
            ->assertSeeHtml('startSpielzug')
            // player confirms starting turn
            ->call('startSpielzug')
            // check that modal is not visible anymore
            ->assertDontSee('Du bist am Zug')
            ->assertDontSeeHtml('startSpielzug');

        // check that player sees updated gameboard
        $this->seeUpdatedGameboard($testCase);

        return $this;
    }

    public function finishTurn(): static {
        $this->testableGameUi
            ->call('spielzugAbschliessen')
            ->assertDontSeeHtml('Du musst erst einen Zeitstein für eine Aktion ausgeben');
        return $this;
    }

    public function openJobBoard(TestCase $testCase): static {
        $topCard = $this->getTopCardFromCategory($testCase, CategoryId::JOBS);
        $topCardRequirements = $topCard->getRequirements();

        $this->testableGameUi
            ->call('showJobOffers')
            ->assertSee([
                'Ein Job kostet Zeit. Pro Jahr bleibt dir ein Zeitstein weniger.',
                'Deine bisher erworbenen Kompetenzen:',
                $topCard->getTitle(),
                number_format($topCard->getGehalt()->value, 2, ',', '.'),
                'Jahresgehalt brutto',
                'Das mache ich!',
                'Voraussetzungen:',
                'Deine bisher erworbenen Kompetenzen:'
            ])
            ->assertSeeHtml([
                '<div class="job-offer__requirements">',
                "<span class=\"sr-only\">Kompetenzsteine im Bereich Bildung & Karriere: $topCardRequirements->bildungKompetenzsteine</span>",
                "<span class=\"sr-only\">Kompetenzsteine im Bereich Freizeit & Soziales: $topCardRequirements->freizeitKompetenzsteine</span>",
                '<div class="badge-with-background">',
                '<div class="job-offers__kompetenzen">'
            ]);

        return $this;
    }

    public function acceptJob(TestCase $testCase) {
        $topCard = $this->getTopCardFromCategory($testCase, CategoryId::JOBS);
        $topCardTitle = $topCard->getTitle();
        $gehalt = number_format($topCard->getGehalt()->value, 2, ",", ".");
        $topCardZeitstein = $topCard->getRequirements()->zeitsteine;

        // check that players amount of Zeitsteine is unchanged
        $zeitsteineBefore = $this->remainingZeitsteine($testCase);

        // check that Zeitsteine in categories are unchanged
        $categoryInfoBeforeAction = $this->getCategoryInfo($testCase);

        // check that player has no job
        $playersJobStatusBeforeAction = $this->getPlayersJobStatus($testCase);
        Assert::assertEquals($playersJobStatusBeforeAction, 'Du hast keinen Job', 'Player has no job');

        $this->testableGameUi
            // check that message is not in Ereignisprotokoll
            ->assertDontSee([
                "nimmt Job '$topCardTitle' an",
                'Mein Job: ',
                "$gehalt €"
            ])
            // play card
            ->call('applyForJob', $topCard->getId()->value)
            // check that message is now logged
            ->assertSee([
                "nimmt Job '$topCardTitle' an",
                'Mein Job: ',
                "$gehalt €"
            ]);

        // check that player has used Zeitsteine
        $zeitsteineAfter = $this->remainingZeitsteine($testCase);
        Assert::assertEquals($zeitsteineBefore - 1 - $topCardZeitstein, $zeitsteineAfter, 'Zeitsteine have been reduced');

        // check that 1 Zeitstein is used for category JOBS
        $categoryInfoAfterAction = $this->getCategoryInfo($testCase);
        foreach ($categoryInfoAfterAction as $name => $category) {
            $categoryInfoAfterAction[$name]['playedThisTurn'] = $category['categoryId'] === CategoryId::JOBS;
        }
        array_map([$this, 'compareUsedSlots'], $categoryInfoAfterAction, $categoryInfoBeforeAction);

        // check that player has a job
        $playersJobStatusAfterAction = $this->getPlayersJobStatus($testCase);
        Assert::assertEquals($playersJobStatusAfterAction, 'Du hast einen Job (Ein Zeitstein ist dauerhaft gebunden)', 'Player has a job');

        return $this;
    }

    private function getPlayersJobStatus(TestCase $testCase): string {
        $playerHasJob = PlayerState::getJobForPlayer($testCase->getGameEvents(), $this->playerId);
        $jobStatus = $playerHasJob === null ? 'Du hast keinen Job' : 'Du hast einen Job (Ein Zeitstein ist dauerhaft gebunden)';
        $this->testableGameUi->assertSeeHtml($jobStatus);

        return $jobStatus;
    }

    private function compareKompetenzsteine($kompetenzsteinAfterAction, $kompetenzsteinBeforeAction): void {
        $category = $kompetenzsteinAfterAction['categoryId']->name;
        if ($kompetenzsteinAfterAction['changedThisTurn']) {
            Assert::assertEquals($kompetenzsteinAfterAction['amount'] - $kompetenzsteinAfterAction['kompetenzsteinChange'], $kompetenzsteinBeforeAction['amount'], "Kompetenzsteine in $category have been increased");
        } else {
            Assert::assertEquals($kompetenzsteinAfterAction['amount'], $kompetenzsteinBeforeAction['amount'], "Kompetenzsteine in $category have not changed");
        }
    }

    private function compareUsedSlots($categoryInfoAfterAction, $categoryInfoBeforeAction): void {
        $category = $categoryInfoAfterAction['categoryId']->name;
        if ($categoryInfoAfterAction['playedThisTurn']) {
            Assert::assertEquals($categoryInfoAfterAction['usedSlots'] - 1, $categoryInfoBeforeAction['usedSlots'], "Zeitsteine in $category have been increased by 1");
        } else {
            Assert::assertEquals($categoryInfoAfterAction['usedSlots'], $categoryInfoBeforeAction['usedSlots'], "Zeitsteine in $category have not changed");
        }
    }
}

