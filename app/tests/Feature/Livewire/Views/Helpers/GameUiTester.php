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
        $lebenszielPhaseId = PlayerState::getCurrentLebenszielphaseIdForPlayer($testCase->getGameEvents(), $this->playerId);
        $pileId = new PileId($categoryId, $lebenszielPhaseId);
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

        $categoryIds = [
            CategoryId::BILDUNG_UND_KARRIERE,
            CategoryId::SOZIALES_UND_FREIZEIT,
            CategoryId::JOBS,
            CategoryId::INVESTITIONEN
        ];

        // get available Slots for categories
        $availableCategorySlots = $this->getAvailableCategorySlots($testCase, $categoryIds);

        // get used Zeitsteinslots for categories before action
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($testCase, $categoryIds);

        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

        // get available Slots for Kompetenzen
        $availableKompetenzSlots = $this->getAvailableKompetenzSlots($testCase);

        // get players Kompetenzen before action
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine($testCase);
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots);

        // check that Kompetenzsteine are unchanged
        $playersKompetenzsteineBeforeAction_ALT = $this->getPlayersKompetenzsteine_ALT($testCase);

        // check that players balance remains the same
        $playersBalanceBeforeAction = $this->getPlayersBalance($testCase);

        // draw and play card
        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCard->getId()->value, $categoryId->value)
            ->assertSee([
                $topCardTitle,
                $topCard->getDescription(),
                // ToDo: Vorzeichen (icon-minus oder icon-plus)
                // ToDo: Zahlenformat in separate function
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

        // get used Zeitsteinslots for categories after action
        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($testCase, $categoryIds);

        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsAfterAction);

        // check that 1 Zeitsteinslot is used in corresponding category
        $this->compareUsedSlots($categoryId, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

        // get players Kompetenzen after action
        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine($testCase);
        dump($playersKompetenzsteineAfterAction);
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineAfterAction, $availableKompetenzSlots);

        // check that player got Kompetenzstein for corresponding category
        // ToDo
        // $topCard->getResourceChanges()->bildungKompetenzsteinChange
        // $topCard->getResourceChanges()->freizeitKompetenzsteinChange
        $this->compareKompetenzsteine();

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

    /**
     * @param TestCase $testCase
     * @param CategoryId[] $categoryIds
     * @return array
     */
    private function getAvailableCategorySlots(TestCase $testCase, array $categoryIds): array {
        $players = $testCase->players;
        $availableCategorySlots = [];

        foreach ($categoryIds as $categoryId) {
            $availableSlots = $testCase
                ->getKonjunkturphaseDefinition()
                ->getKompetenzbereichByCategory($categoryId)
                ->zeitslots
                ->getAmountOfZeitslotsForPlayerCount(count($players));
            $availableCategorySlots[$categoryId->name] = $availableSlots;
        }

        return $availableCategorySlots;
    }

    /**
     * @param TestCase $testCase
     * @param CategoryId[] $categoryIds
     * @return array
     */
    private function getOccupiedCategorySlots(TestCase $testCase, array $categoryIds): array {
        $players = $testCase->players;
        $usedCategorySlots = [];

        foreach ($categoryIds as $categoryId) {
            $usedSlots = 0;
            foreach ($players as $player) {
                $playerName = PlayerState::getNameForPlayer($testCase->getGameEvents(), $player);
                $placedZeitsteineByPlayer = PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($testCase->getGameEvents(), $player, $categoryId);
                $usedSlots += $placedZeitsteineByPlayer;
                $usedCategorySlots[$categoryId->name]['players'][$playerName] = $placedZeitsteineByPlayer;
            }
            $usedCategorySlots[$categoryId->name]['total'] = $usedSlots;
        }

        return $usedCategorySlots;
    }

    private function assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlots): void {
        foreach ($availableCategorySlots as $categoryName => $availableSlots) {
            $slotsUsedByPlayer = [];
            foreach ($usedCategorySlots[$categoryName]['players'] as $playerName => $amount) {
                $slotsUsedByPlayer[] = $playerName . ': ' . $amount;
            }
            $this->testableGameUi->assertSeeHtml(
                $usedCategorySlots[$categoryName]['total'] . ' von ' . $availableSlots . ' Zeitsteinen wurden platziert. ' . implode(', ', $slotsUsedByPlayer)
            );
        }
    }

    private function getAvailableKompetenzSlots(TestCase $testCase): array {
        $currentLebenszielphaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer(
            $testCase->getGameEvents(),
            $this->playerId
        );
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $currentLebenszielphaseDefinition->bildungsKompetenzSlots,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $currentLebenszielphaseDefinition->freizeitKompetenzSlots
        ];
    }

    private function getPlayersKompetenzsteine(TestCase $testCase): array {
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $playerResources->bildungKompetenzsteinChange,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $playerResources->freizeitKompetenzsteinChange
        ];
    }

    private function assertVisibilityOfKompetenzen($playersKompetenzsteine, $availableKompetenzSlots): void {
        foreach ($playersKompetenzsteine as $categoryName => $achievedAmount) {
            // replace '&' with '&amp;'
            $categoryNameAdapted = str_replace("&", "&amp;", $categoryName);
            $availableAmount = $availableKompetenzSlots[$categoryName];
            $this->testableGameUi->assertSeeHtml(
                "Deine Kompetenzsteine im Bereich $categoryNameAdapted: $achievedAmount von $availableAmount"
            );
        }
    }

    private function getPlayersKompetenzsteine_ALT(TestCase $testCase): array {
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

        return $categories;
    }

    private function getPlayersBalance(TestCase $testCase): float {
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $balance = $playerResources->guthabenChange->value;
        $this->testableGameUi->assertSee(number_format($balance, 2, ',', '.'));

        return $balance;
    }

    private function compareUsedSlots($categoryId, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction): void {
        foreach ($usedCategorySlotsAfterAction as $categoryName => $usedSlots) {
            if ($categoryName === $categoryId->name) {
                Assert::assertEquals(
                    $usedCategorySlotsAfterAction[$categoryName]['total'] - 1,
                    $usedCategorySlotsBeforeAction[$categoryName]['total'],
                    "Zeitsteinslots in $categoryName have been increased by 1"
                );
            } else {
                Assert::assertEquals(
                    $usedCategorySlotsAfterAction[$categoryName]['total'],
                    $usedCategorySlotsBeforeAction[$categoryName]['total'],
                    "Zeitsteinslots in $categoryName have not changed"
                );
            }
        }
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

        $categoryIds = [
            CategoryId::BILDUNG_UND_KARRIERE,
            CategoryId::SOZIALES_UND_FREIZEIT,
            CategoryId::JOBS,
            CategoryId::INVESTITIONEN
        ];

        // get available Slots for categories
        $availableCategorySlots = $this->getAvailableCategorySlots($testCase, $categoryIds);

        // get used Zeitsteinslots for categories before action
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($testCase, $categoryIds);

        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

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

        // get used Zeitsteinslots for categories after action
        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($testCase, $categoryIds);

        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsAfterAction);

        // check that 1 Zeitsteinslot is used for category JOBS
        $this->compareUsedSlots(CategoryId::JOBS, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

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

    public function openInvestmentsOverview(): static {
        $this->testableGameUi
            // player opens investments overview
            ->call('toggleInvestitionenSelectionModal')
            ->assertSeeHtml([
                '<div class="modal__backdrop" wire:click=toggleInvestitionenSelectionModal()></div>',
                '<h2 class="modal__header" id="modal-headline">',
//                '<h2 class="modal__body" id="modal-content">',
                '<div class="investitionen-overview">',
                '<button class="card" wire:click="toggleStocksModal()">',
                '<h4 class="card__title">Aktien</h4>',
                '<button class="card" wire:click="toggleETFModal()">',
                '<h4 class="card__title">ETF</h4>',
                '<button class="card" wire:click="toggleCryptoModal()">',
                '<h4 class="card__title">Krypto</h4>',
                '<button class="card" wire:click="toggleImmobilienModal()">',
                '<h4 class="card__title">Immobilien</h4>'
            ])
            // player chooses stocks
            ->call('toggleStocksModal')
            ->assertSee([
                'Aktien sind Anteilsscheine an einzelnen Unternehmen. Ihr Wert schwankt abhängig von',
                'Gewinnen, Management-Entscheidungen und aktuellen Nachrichten. Sie bieten Chancen auf',
                'Dividenden und Kursgewinne, bergen jedoch auch das Risiko unternehmensspezifischer Rückschläge.',
                'Merfedes-Penz',
                'BetaPear',
                '50,00 €',
                'kaufen',
                'verkaufen'
            ])
            ->assertSeeHtml([
                '<div class="modal__backdrop" wire:click=toggleStocksModal()></div>',
                '<div class="modal__close-button">',
                '<h2 class="modal__header" id="modal-headline">',
//                '<h2 class="modal__body" id="modal-content">',
            ]);

        return $this;
    }

    public function buyStocks() {

        return $this;
    }

    private function compareKompetenzsteine_ALT($kompetenzsteinAfterAction, $kompetenzsteinBeforeAction): void {
        $category = $kompetenzsteinAfterAction['categoryId']->name;
        if ($kompetenzsteinAfterAction['changedThisTurn']) {
            Assert::assertEquals($kompetenzsteinAfterAction['amount'] - $kompetenzsteinAfterAction['kompetenzsteinChange'], $kompetenzsteinBeforeAction['amount'], "Kompetenzsteine in $category have been increased");
        } else {
            Assert::assertEquals($kompetenzsteinAfterAction['amount'], $kompetenzsteinBeforeAction['amount'], "Kompetenzsteine in $category have not changed");
        }
    }

    private function compareKompetenzsteine() {

    }
}
