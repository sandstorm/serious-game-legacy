<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Investments\InvestmentFinder;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
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
        $playerColorClass = PlayerState::getPlayerColorClass($testCase->getGameEvents(), $this->playerId);
        $playersGuthabenFormatted = PlayerState::getGuthabenForPlayer($testCase->getGameEvents(), $this->playerId)->format();

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

            ]);

        $this->assertVisibilityOfBalance($testCase);
    }

    /**
     * @param TestCase $testCase
     * @param CategoryId $categoryId
     * @return KategorieCardDefinition | JobCardDefinition
     */
    private function getTopCardFromCategory(TestCase $testCase, CategoryId $categoryId): KategorieCardDefinition|JobCardDefinition {
        $lebenszielPhaseId = PlayerState::getCurrentLebenszielphaseIdForPlayer(
            $testCase->getGameEvents(),
            $this->playerId
        );
        $pileId = new PileId($categoryId, $lebenszielPhaseId);
        $topCardIdForPile = PileState::topCardIdForPile($testCase->getGameEvents(), $pileId);
        return CardFinder::getInstance()->getCardById(new CardId($topCardIdForPile->value));
    }

    private function assertVisibilityOfBalance(TestCase $testCase): void {
        $playerColorClass = PlayerState::getPlayerColorClass($testCase->getGameEvents(), $this->playerId);
        $playersGuthabenFormatted = PlayerState::getGuthabenForPlayer($testCase->getGameEvents(), $this->playerId)->format();

        $this->testableGameUi->assertSeeHtml(
            "<button title=\"Moneysheet öffnen\" class=\"button button--type-primary $playerColorClass\" wire:click=\"showMoneySheet()\">
                        $playersGuthabenFormatted"
        );
    }

    public function checkThatSidebarActionsAreVisible(bool $actionsAreVisible, TestCase $testCase): static {
        $playerColorClass = PlayerState::getPlayerColorClass($testCase->getGameEvents(), $this->playerId);

        if ($actionsAreVisible) {
            $this->testableGameUi
                ->assertSeeHtml([
                    "<button
                class=\"button button--type-secondary \"
                wire:click=\"showTakeOutALoan()\">
                    <span>Kredit aufnehmen</span> <i class=\"icon-dots\" aria-hidden=\"true\"></i>
            </button>",
                    "<button class=\"button button--type-secondary\" wire:click=\"showExpensesTab('insurances')\">
                <span>Versicherung abschließen</span> <i class=\"icon-dots\" aria-hidden=\"true\"></i>
            </button>",
                    " <button
                type=\"button\"
                class=\"button button--type-primary button--disabled $playerColorClass\"
                wire:click=\"spielzugAbschliessen()\">
                Spielzug beenden
            </button>"
                ]);
        } else {
            $this->testableGameUi
                ->assertDontSeeHtml([
                    "<button
                class=\"button button--type-secondary \"
                wire:click=\"showTakeOutALoan()\">
                    <span>Kredit aufnehmen</span> <i class=\"icon-dots\" aria-hidden=\"true\"></i>
            </button>",
                    "<button class=\"button button--type-secondary\" wire:click=\"showExpensesTab('insurances')\">
                <span>Versicherung abschließen</span> <i class=\"icon-dots\" aria-hidden=\"true\"></i>
            </button>",
                    " <button
                type=\"button\"
                class=\"button button--type-primary button--disabled $playerColorClass\"
                wire:click=\"spielzugAbschliessen()\">
                Spielzug beenden
            </button>"
                ]);
        }

        return $this;
    }

    public function drawAndPlayCard(TestCase $testCase, CategoryId $categoryId) {
        // get top card from category
        $topCard = $this->getTopCardFromCategory($testCase, $categoryId);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;
        $topCardZeitstein = $topCard->getResourceChanges()->zeitsteineChange;

        // get players available Zeitsteine
        $availableZeitsteine = $this->getAvailableZeitsteine($testCase);
        // get players remaining Zeitsteine before action
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine($testCase);
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

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

        // get players balance before action
        $playersBalanceBeforeAction = $this->getPlayersBalance($testCase);
        $this->assertVisibilityOfBalance($testCase);

        // draw and play card
        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCard->getId()->value, $categoryId->value)
            ->assertSee([
                $topCardTitle,
                $topCard->getDescription(),
                $topCardGuthabenChange === 0 ? '' : $this->numberFormatMoney(abs($topCardGuthabenChange)),
                'Karte spielen'
            ])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte '$topCardTitle'")
            // play card
            ->call('activateCard', $categoryId->value)
            // check that message is now logged
            ->assertSee("spielt Karte '$topCardTitle'");

        // get players remaining Zeitsteine after action
        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine($testCase);
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 1 + $topCardZeitstein,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        // get used Zeitsteinslots for categories after action
        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($testCase, $categoryIds);
        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used in corresponding category
        $this->compareUsedSlots($categoryId, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

        // get players Kompetenzen after action
        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine($testCase);
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineAfterAction, $availableKompetenzSlots);
        // check that player got Kompetenzstein for corresponding category
        $topCardKompetenzsteinChange = $this->getTopCardKompetenzsteinChange($topCard);
        $this->compareKompetenzsteine(
            $topCardKompetenzsteinChange,
            $playersKompetenzsteineBeforeAction,
            $playersKompetenzsteineAfterAction
        );

        // check that player has paid
        $playersBalanceAfterAction = $this->getPlayersBalance($testCase);
        $this->assertVisibilityOfBalance($testCase);
        Assert::assertEquals(
            $playersBalanceBeforeAction + $topCardGuthabenChange,
            $playersBalanceAfterAction,
            "Balance has been changed by $topCardGuthabenChange"
        );

        return $this;
    }

    private function getAvailableZeitsteine(TestCase $testCase): int {
        return $testCase
            ->getKonjunkturphaseDefinition()
            ->zeitsteine
            ->getAmountOfZeitsteineForPlayer(count($testCase->players));
    }

    private function getPlayersZeitsteine(TestCase $testCase): int {
        return PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId)->zeitsteineChange;
    }

    private function assertVisibilityOfZeitsteine($currentZeitsteine, $availableZeitsteine): void {
        $this->testableGameUi->assertSeeHtml(
            $this->playerName . ' hat noch ' . $currentZeitsteine . ' von ' . $availableZeitsteine . ' Zeitsteinen übrig.'
        );
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

    private function getPlayersBalance(TestCase $testCase): float {
        $playerResources = PlayerState::getResourcesForPlayer($testCase->getGameEvents(), $this->playerId);
        $balance = $playerResources->guthabenChange->value;
        $this->testableGameUi->assertSee($this->numberFormatMoney($balance));

        return $balance;
    }

    private function numberFormatMoney($amount): string {
        return number_format(($amount), 2, ',', '.');
    }

    private function compareUsedSlots($categoryId, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction): void {
        foreach ($usedCategorySlotsAfterAction as $categoryName => $usedSlots) {
            if ($categoryName === $categoryId->name) {
                Assert::assertEquals(
                    $usedSlots['total'] - 1,
                    $usedCategorySlotsBeforeAction[$categoryName]['total'],
                    "Zeitsteinslots in $categoryName have been increased by 1"
                );
            } else {
                Assert::assertEquals(
                    $usedSlots['total'],
                    $usedCategorySlotsBeforeAction[$categoryName]['total'],
                    "Zeitsteinslots in $categoryName have not changed"
                );
            }
        }
    }

    private function getTopCardKompetenzsteinChange(KategorieCardDefinition $topCard): array {
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $topCard->getResourceChanges()->bildungKompetenzsteinChange,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $topCard->getResourceChanges()->freizeitKompetenzsteinChange
        ];
    }

    private function compareKompetenzsteine(
        $topCardKompetenzsteinChange,
        $playersKompetenzsteineBeforeAction,
        $playersKompetenzsteineAfterAction
    ): void {
        foreach ($playersKompetenzsteineAfterAction as $categoryName => $amount) {
            Assert::assertEquals(
                $playersKompetenzsteineBeforeAction[$categoryName] + $topCardKompetenzsteinChange[$categoryName],
                $playersKompetenzsteineAfterAction[$categoryName],
                'Kompetenzsteine have been changed by values displayed on card'
            );
        }
    }

    public function tryToPlayCardWhenItIsNotThePlayersTurn(TestCase $testCase, CategoryId $categoryId): void {
        // get top card from category
        $topCard = $this->getTopCardFromCategory($testCase, $categoryId);

        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCard->getId()->value, $categoryId->value)
            // play card
            ->call('activateCard', $categoryId->value)
            // show error message
            ->assertSee('Du bist gerade nicht dran');
    }

    public function startTurn(TestCase $testCase): static {
        $playerColorClass = PlayerState::getPlayerColorClass($testCase->getGameEvents(), $this->playerId);

        $this->testableGameUi
            // check that modal is visible
            ->assertSeeHtml([
                '<div class="modal__backdrop"></div>',
                '<div class="modal__body" id="mandatory-modal-content">',
                '<h3>Du bist am Zug!</h3>',
                "<button type=\"button\"
        class=\"button button--type-primary $playerColorClass\"
        wire:click=\"startSpielzug()\"
    >
        Ok
    </button>"
            ])
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
                $this->numberFormatMoney($topCard->getGehalt()->value),
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

    public function acceptJobWhenPlayerCurrentlyHasNoJob(TestCase $testCase) {
        $topCard = $this->getTopCardFromCategory($testCase, CategoryId::JOBS);
        $topCardTitle = $topCard->getTitle();
        $gehalt = $this->numberFormatMoney($topCard->getGehalt()->value);

        // get players available Zeitsteine
        $availableZeitsteine = $this->getAvailableZeitsteine($testCase);
        // get players remaining Zeitsteine before action
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine($testCase);
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

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

        // get players remaining Zeitsteine after action
        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine($testCase);
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 2,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        // get used Zeitsteinslots for categories after action
        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($testCase, $categoryIds);
        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used for category JOBS
        $this->compareUsedSlots(
            CategoryId::JOBS,
            $usedCategorySlotsBeforeAction,
            $usedCategorySlotsAfterAction
        );

        // check that player has a job
        $playersJobStatusAfterAction = $this->getPlayersJobStatus($testCase);
        Assert::assertEquals(
            $playersJobStatusAfterAction,
            'Du hast einen Job (Ein Zeitstein ist dauerhaft gebunden)',
            'Player has a job'
        );

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
            ->call('toggleInvestitionenSelectionModal')
            ->assertSeeHtml([
                '<div class="modal__backdrop" wire:click=toggleInvestitionenSelectionModal()></div>',
                '<div class="modal__close-button">',
                '<h2 class="modal__header" id="modal-headline">',
                '<div class="modal__body" id="modal-content">',
                '<div class="investitionen-overview">',
                '<button class="card" wire:click="toggleStocksModal()">',
                '<h4 class="card__title">Aktien</h4>',
                '<button class="card" wire:click="toggleETFModal()">',
                '<h4 class="card__title">ETF</h4>',
                '<button class="card" wire:click="toggleCryptoModal()">',
                '<h4 class="card__title">Krypto</h4>',
                '<button class="card" wire:click="toggleImmobilienModal()">',
                '<h4 class="card__title">Immobilien</h4>'
            ]);

        return $this;
    }

    public function chooseStocks(TestCase $testCase): static {
        $playerColorClass = PlayerState::getPlayerColorClass($testCase->getGameEvents(), $this->playerId);
        $firstStock = InvestmentId::MERFEDES_PENZ;
        $secondStock = InvestmentId::BETA_PEAR;

        $this->testableGameUi
            ->call('toggleStocksModal')
            ->assertSee([
                'Aktien sind Anteilsscheine an einzelnen Unternehmen. Ihr Wert schwankt abhängig von',
                'Gewinnen, Management-Entscheidungen und aktuellen Nachrichten. Sie bieten Chancen auf',
                'Dividenden und Kursgewinne, bergen jedoch auch das Risiko unternehmensspezifischer Rückschläge.',
            ])
            ->assertSeeHtml([
                '<div class="modal__backdrop" wire:click=toggleStocksModal()></div>',
                '<div class="modal__close-button">',
                '<h2 class="modal__header" id="modal-headline">',
                '<div class="modal__body" id="modal-content">',
                "<h4>$firstStock->value</h4>",
                InvestmentPriceState::getCurrentInvestmentPrice($testCase->getGameEvents(), $firstStock)->format(),
                "<button
            type=\"button\"
            class=\"button button--type-primary $playerColorClass\"
            wire:click=\"showBuyInvestmentOfType('$firstStock->value')\"
        >
            kaufen
        </button>",
                "<button
            type=\"button\"
            class=\"button button--type-outline-primary button--disabled $playerColorClass\"
            wire:click=\"showSellInvestmentOfType('$firstStock->value')\"
        >
            verkaufen
        </button>",
                "<h4>$secondStock->value</h4>",
                InvestmentPriceState::getCurrentInvestmentPrice($testCase->getGameEvents(), $secondStock)->format(),
                "<button
            type=\"button\"
            class=\"button button--type-primary $playerColorClass\"
            wire:click=\"showBuyInvestmentOfType('$secondStock->value')\"
        >
            kaufen
        </button>",
                "<button
            type=\"button\"
            class=\"button button--type-outline-primary button--disabled $playerColorClass\"
            wire:click=\"showSellInvestmentOfType('$secondStock->value')\"
        >
            verkaufen
        </button>",
            ]);
        return $this;
    }

    public function buyStocks(TestCase $testCase, InvestmentId $investmentId, int $amount): static {
        $investmentDefinition = InvestmentFinder::findInvestmentById($investmentId);
        $currentInvestmentPrice = InvestmentPriceState::getCurrentInvestmentPrice($testCase->getGameEvents(), $investmentId);
        $currentInvestmentPriceFormatted = $this->numberFormatMoney($currentInvestmentPrice->value);
        $investedMoney = $amount * $currentInvestmentPrice->value;
        $dividende = $investmentId === InvestmentId::MERFEDES_PENZ
            ? KonjunkturphaseState::getCurrentKonjunkturphase($testCase->getGameEvents())->getDividend()->format()
            : "/";

        // get players balance before action
        $playersBalanceBeforeAction = $this->getPlayersBalance($testCase);
        $this->assertVisibilityOfBalance($testCase);

        $this->testableGameUi
            ->call('showBuyInvestmentOfType', $investmentId->value)
            ->assertSee([
                InvestmentFinder::findInvestmentById($investmentId)->description,
                'Stückzahl',
                'Summe Kauf',
                'Langfristige Tendenz:',
                'Kursschwankungen:',
                'Dividende pro Aktie:'
            ])
            ->assertSeeHtml([
                '<div class="modal__backdrop" wire:click=toggleStocksModal()></div>',
                '<div class="modal__close-button">',
                '<h2 class="modal__header" id="modal-headline">',
                "Kauf - $investmentId->value",
                '<div class="modal__body" id="modal-content">',
                $currentInvestmentPrice->format(),
                '0,00 €',
                "<strong>$investmentDefinition->longTermTrend%</strong>",
                "<strong>$investmentDefinition->fluctuations%</strong>",
                "<strong>$dividende</strong>"
            ])
            // set amount
            ->set('buyInvestmentsForm.amount', $amount)
            // buy stocks
            ->call('buyInvestments', $investmentId->value)
            ->assertDontSeeHtml([
                '<div class="modal__backdrop" wire:click=toggleStocksModal()></div>',
                '<div class="modal__close-button">',
                '<h2 class="modal__header" id="modal-headline">',
                "Kauf - $investmentId->value",
                '<div class="modal__body" id="modal-content">',
                $currentInvestmentPrice->format(),
                "<strong>$investmentDefinition->longTermTrend%</strong>",
                "<strong>$investmentDefinition->fluctuations%</strong>",
                "<strong>$dividende</strong>"
            ])
            ->assertSee(
                "Investiert in '$investmentId->value' und kauft $amount Anteile zum Preis von $currentInvestmentPriceFormatted €"
            );

        // check that players balance has changed
        $playersBalanceAfterAction = $this->getPlayersBalance($testCase);
        $this->assertVisibilityOfBalance($testCase);
        Assert::assertEquals(
            $playersBalanceAfterAction,
            $playersBalanceBeforeAction - $investedMoney,
            "Balance has been changed by investment"
        );

        return $this;
    }

    public function sellStocksThatOtherPlayerIsBuying(InvestmentId $stockId, TestCase $testCase): void {

        $playerColorClass = PlayerState::getPlayerColorClass($testCase->getGameEvents(), $this->playerId);
        $lastInvestmentBoughtByAPlayer = $testCase->getGameEvents()->findLast(PlayerHasBoughtInvestment::class);
        $nameOfPlayerWhoBoughtInvestment = PlayerState::getNameForPlayer(
            $testCase->getGameEvents(),
            $lastInvestmentBoughtByAPlayer->playerId
        );

        $this->testableGameUi
            ->assertSeeHtml([
                '<div class="modal__backdrop"></div>',
                "<h2 class=\"modal__header\" id=\"mandatory-modal-headline\">
                    <span>
        Verkauf - $stockId->value <i class=\"icon-aktien\" aria-hidden=\"true\"></i>
    </span>
            </h2>",
                "<div class=\"modal__body\" id=\"mandatory-modal-content\">
                <h4>$nameOfPlayerWhoBoughtInvestment hat in $stockId->value investiert!</h4>",
                "<button type=\"button\"
            class=\"button button--type-outline-primary $playerColorClass\"
            wire:click=\"closeSellInvestmentsModal()\"
    >
        Ich möchte nichts verkaufen
    </button>"
            ])
            ->assertDontSeeHtml([
                '<div class="modal__close-button">'
            ]);

//        Todo: überprüfen, ob Player Aktien besitzt --> abhängig davon wird anderer Text gerendert (siehe unten)
        $this->testableGameUi
            ->assertSeeHtml(
                "Du hast keine Anteile vom Typ $stockId->value."
            );

        $this->testableGameUi
            ->call('closeSellInvestmentsModal');

// ToDo: Wenn Spieler Aktien von diesem Typ besitzt (investitionen-sell-form.blade.php)
//
//        <p>
//        Du hast aktuell <strong>{{ $this->sellInvestmentsForm->amountOwned }}</strong> Anteile vom Typ
//        <strong>{{ $this->sellInvestmentsForm->investmentId }}</strong> in deinem Besitz.
//    </p>

//        ToDo: wenn Spieler mehr als 0 Aktien von dem Typ besitzt (investitionen-sell-after-purchase-modal.blade-php)
//        <p>
//        Du kannst jetzt deine Anteile verkaufen.
//        </p>

        // ToDo: Wenn Spieler keine Aktien diesen Types besitzt (investitionen-sell-form.blade.php)
//        <p>
//        Du hast keine Anteile vom Typ {{ $this->sellInvestmentsForm->investmentId }}.
//    </p>

//        ToDo: wenn Spieler Anteile verkaufen kann (submit.blade.php + investitionen-sell-form.blade.php + investitionen-sell-after-purchase-modal.blade-php)
//        "<button
//    type=\"submit\"
//    class=\"button button--type-primary $playerColorClass\"
//    disabled wire:dirty.remove.attr=\"disabled\"
//>
//    Anteile verkaufen
//</button>",

    }

}
