<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
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
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
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
    private string $playerColorClass;

    public function __construct(private TestCase $testCase, private PlayerId $playerId, private string $playerName) {
        $this->testableGameUi = Livewire::test(GameUi::class, [
            'gameId' => $this->testCase->gameId,
            'myself' => $this->playerId
        ]);
        $this->playerColorClass = PlayerState::getPlayerColorClass($this->testCase->getGameEvents(), $this->playerId);
    }

    public function startGame(): static {
        $this->testableGameUi
            ->assertSee([
                'Eine neue Konjunkturphase beginnt.',
                'Das nächste Szenario ist:',
                $this->testCase->getKonjunkturphaseDefinition()->type->value
            ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->assertSee([
                $this->testCase->getKonjunkturphaseDefinition()->type->value,
                $this->testCase->getKonjunkturphaseDefinition()->description
            ])
            ->call('startKonjunkturphaseForPlayer');

        $this->seeUpdatedGameboard();

        return $this;
    }

    private function seeUpdatedGameboard(): void {
        $this->testableGameUi
            ->assertSee([
                'Bildung & Karriere',
                'Freizeit & Soziales',
                'Beruf',
                'Finanzen',
                'Konjunktur',
                $this->testCase->getKonjunkturphaseDefinition()->type->value,
                'Dein Lebensziel',
                'Ereignisprotokoll:',
                'Eine neue Konjunkturphase \'' . $this->testCase->getKonjunkturphaseDefinition()->name . '\' beginnt.',
                $this->getTopCardFromCategory(CategoryId::BILDUNG_UND_KARRIERE)->getTitle(),
                $this->getTopCardFromCategory(CategoryId::SOZIALES_UND_FREIZEIT)->getTitle(),
                'Jobbörse',
                'Investitionen',
                'Weiterbildung',
                'Minijob',

            ]);

        $this->assertVisibilityOfBalance();
    }

    /**
     * @param CategoryId $categoryId
     * @return KategorieCardDefinition | JobCardDefinition | MinijobCardDefinition
     */
    private function getTopCardFromCategory(CategoryId $categoryId): KategorieCardDefinition|JobCardDefinition|MinijobCardDefinition {
        $lebenszielPhaseId = PlayerState::getCurrentLebenszielphaseIdForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        );

        if ($categoryId === CategoryId::MINIJOBS) {
            $pileId = new PileId($categoryId);
        } else {
            $pileId = new PileId($categoryId, $lebenszielPhaseId);
        }

        $topCardIdOnPile = PileState::topCardIdForPile($this->testCase->getGameEvents(), $pileId);

        return CardFinder::getInstance()->getCardById(new CardId($topCardIdOnPile->value));
    }

    private function assertVisibilityOfBalance(): void {
        $playersGuthabenFormatted = PlayerState::getGuthabenForPlayer($this->testCase->getGameEvents(), $this->playerId)->format();

        $this->testableGameUi->assertSeeHtml(
            "<button title=\"Moneysheet öffnen\" class=\"button button--type-primary $this->playerColorClass\" wire:click=\"showMoneySheet()\">
                        $playersGuthabenFormatted"
        );
//        dump($playersGuthabenFormatted);
    }

    public function checkThatSidebarActionsAreVisible(bool $actionsAreVisible): static {
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
                class=\"button button--type-primary button--disabled $this->playerColorClass\"
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
                class=\"button button--type-primary button--disabled $this->playerColorClass\"
                wire:click=\"spielzugAbschliessen()\">
                Spielzug beenden
            </button>"
                ]);
        }

        return $this;
    }

    public function drawAndPlayCard(CategoryId $categoryId) {
        // get top card from category
        $topCard = $this->getTopCardFromCategory($categoryId);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;
        $topCardZeitstein = $topCard->getResourceChanges()->zeitsteineChange;

        // get players available Zeitsteine
        $availableZeitsteine = $this->getAvailableZeitsteine();
        // get players remaining Zeitsteine before action
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        $categoryIds = [
            CategoryId::BILDUNG_UND_KARRIERE,
            CategoryId::SOZIALES_UND_FREIZEIT,
            CategoryId::JOBS,
            CategoryId::INVESTITIONEN
        ];

        // get available Slots for categories
        $availableCategorySlots = $this->getAvailableCategorySlots($categoryIds);
        // get used Zeitsteinslots for categories before action
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($categoryIds);
        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

        // get available Slots for Kompetenzen
        $availableKompetenzSlots = $this->getAvailableKompetenzSlots();
        // get players Kompetenzen before action
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots);

        // get players balance before action
        $playersBalanceBeforeAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();

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
        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine();
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 1 + $topCardZeitstein,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        // get used Zeitsteinslots for categories after action
        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($categoryIds);
        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used in corresponding category
        $this->compareUsedSlots($categoryId, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

        // get players Kompetenzen after action
        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineAfterAction, $availableKompetenzSlots);
        // check that player got Kompetenzstein for corresponding category
        $topCardKompetenzsteinChange = $this->getTopCardKompetenzsteinChange($topCard);
        $this->compareKompetenzsteine(
            $topCardKompetenzsteinChange,
            $playersKompetenzsteineBeforeAction,
            $playersKompetenzsteineAfterAction
        );

        // check that player has paid
        $playersBalanceAfterAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();
        Assert::assertEquals(
            $playersBalanceBeforeAction + $topCardGuthabenChange,
            $playersBalanceAfterAction,
            "Balance has been changed by $topCardGuthabenChange"
        );

        return $this;
    }

    private function getAvailableZeitsteine(): int {
        return $this->testCase
            ->getKonjunkturphaseDefinition()
            ->zeitsteine
            ->getAmountOfZeitsteineForPlayer(count($this->testCase->players));
    }

    private function getPlayersZeitsteine(): int {
        return PlayerState::getResourcesForPlayer($this->testCase->getGameEvents(), $this->playerId)->zeitsteineChange;
    }

    private function assertVisibilityOfZeitsteine($currentZeitsteine, $availableZeitsteine): void {
        $this->testableGameUi->assertSeeHtml(
            $this->playerName . ' hat noch ' . $currentZeitsteine . ' von ' . $availableZeitsteine . ' Zeitsteinen übrig.'
        );
//        dump($this->playerName . ' hat noch ' . $currentZeitsteine . ' von ' . $availableZeitsteine . ' Zeitsteinen übrig.');
    }

    /**
     * @param CategoryId[] $categoryIds
     * @return array
     */
    private function getAvailableCategorySlots(array $categoryIds): array {
        $players = $this->testCase->players;
        $availableCategorySlots = [];

        foreach ($categoryIds as $categoryId) {
            $availableSlots = $this->testCase
                ->getKonjunkturphaseDefinition()
                ->getKompetenzbereichByCategory($categoryId)
                ->zeitslots
                ->getAmountOfZeitslotsForPlayerCount(count($players));
            $availableCategorySlots[$categoryId->name] = $availableSlots;
        }

        return $availableCategorySlots;
    }

    /**
     * @param CategoryId[] $categoryIds
     * @return array
     */
    private function getOccupiedCategorySlots(array $categoryIds): array {
        $players = $this->testCase->players;
        $usedCategorySlots = [];

        foreach ($categoryIds as $categoryId) {
            $usedSlots = 0;
            foreach ($players as $player) {
                $playerName = PlayerState::getNameForPlayer($this->testCase->getGameEvents(), $player);
                $placedZeitsteineByPlayer = PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory(
                    $this->testCase->getGameEvents(),
                    $player,
                    $categoryId
                );
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

    private function getAvailableKompetenzSlots(): array {
        $currentLebenszielphaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        );
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $currentLebenszielphaseDefinition->bildungsKompetenzSlots,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $currentLebenszielphaseDefinition->freizeitKompetenzSlots
        ];
    }

    private function getPlayersKompetenzsteine(): array {
        $playerResources = PlayerState::getResourcesForPlayer($this->testCase->getGameEvents(), $this->playerId);
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

    private function getPlayersBalance(): float {
        $playerResources = PlayerState::getResourcesForPlayer($this->testCase->getGameEvents(), $this->playerId);
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

    public function tryToPlayCardWhenItIsNotThePlayersTurn(CategoryId $categoryId): void {
        // get top card from category
        $topCard = $this->getTopCardFromCategory($categoryId);

        $this->testableGameUi
            // draw a card
            ->call('showCardActions', $topCard->getId()->value, $categoryId->value)
            // play card
            ->call('activateCard', $categoryId->value)
            // show error message
            ->assertSee('Du bist gerade nicht dran');
    }

    public function startTurn(): static {
        $this->testableGameUi
            // check that modal is visible
            ->assertSeeHtml([
                '<div class="modal__backdrop"></div>',
                '<div class="modal__body" id="mandatory-modal-content">',
                '<h3>Du bist am Zug!</h3>',
                "<button type=\"button\"
        class=\"button button--type-primary $this->playerColorClass\"
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
        $this->seeUpdatedGameboard();

        return $this;
    }

    public function finishTurn(): static {
        $this->testableGameUi
            ->call('spielzugAbschliessen')
            ->assertDontSeeHtml('Du musst erst einen Zeitstein für eine Aktion ausgeben');
        return $this;
    }

    public function openJobBoard(): static {
        $topCard = $this->getTopCardFromCategory(CategoryId::JOBS);
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

    public function acceptJobWhenPlayerCurrentlyHasNoJob() {
        $topCard = $this->getTopCardFromCategory(CategoryId::JOBS);
        $topCardTitle = $topCard->getTitle();
        $gehalt = $this->numberFormatMoney($topCard->getGehalt()->value);

        // get players available Zeitsteine
        $availableZeitsteine = $this->getAvailableZeitsteine();
        // get players remaining Zeitsteine before action
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        $categoryIds = [
            CategoryId::BILDUNG_UND_KARRIERE,
            CategoryId::SOZIALES_UND_FREIZEIT,
            CategoryId::JOBS,
            CategoryId::INVESTITIONEN
        ];

        // get available Slots for categories
        $availableCategorySlots = $this->getAvailableCategorySlots($categoryIds);
        // get used Zeitsteinslots for categories before action
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($categoryIds);
        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

        // check that player has no job
        $playersJobStatusBeforeAction = $this->getPlayersJobStatus();
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
        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine();
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 2,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        // get used Zeitsteinslots for categories after action
        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($categoryIds);
        // check that Zeitsteinslots are rendered correctly
        $this->assertVisibilityOfSlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used for category JOBS
        $this->compareUsedSlots(
            CategoryId::JOBS,
            $usedCategorySlotsBeforeAction,
            $usedCategorySlotsAfterAction
        );

        // check that player has a job
        $playersJobStatusAfterAction = $this->getPlayersJobStatus();
        Assert::assertEquals(
            $playersJobStatusAfterAction,
            'Du hast einen Job (Ein Zeitstein ist dauerhaft gebunden)',
            'Player has a job'
        );

        return $this;
    }

    private function getPlayersJobStatus(): string {
        $playerHasJob = PlayerState::getJobForPlayer($this->testCase->getGameEvents(), $this->playerId);
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

    public function chooseStocks(): static {
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
                InvestmentPriceState::getCurrentInvestmentPrice($this->testCase->getGameEvents(), $firstStock)->format(),
                "<button
            type=\"button\"
            class=\"button button--type-primary $this->playerColorClass\"
            wire:click=\"showBuyInvestmentOfType('$firstStock->value')\"
        >
            kaufen
        </button>",
                "<button
            type=\"button\"
            class=\"button button--type-outline-primary button--disabled $this->playerColorClass\"
            wire:click=\"showSellInvestmentOfType('$firstStock->value')\"
        >
            verkaufen
        </button>",
                "<h4>$secondStock->value</h4>",
                InvestmentPriceState::getCurrentInvestmentPrice($this->testCase->getGameEvents(), $secondStock)->format(),
                "<button
            type=\"button\"
            class=\"button button--type-primary $this->playerColorClass\"
            wire:click=\"showBuyInvestmentOfType('$secondStock->value')\"
        >
            kaufen
        </button>",
                "<button
            type=\"button\"
            class=\"button button--type-outline-primary button--disabled $this->playerColorClass\"
            wire:click=\"showSellInvestmentOfType('$secondStock->value')\"
        >
            verkaufen
        </button>",
            ]);
        return $this;
    }

    public function buyStocks(InvestmentId $investmentId, int $amount): static {
        $investmentDefinition = InvestmentFinder::findInvestmentById($investmentId);
        $currentInvestmentPrice = InvestmentPriceState::getCurrentInvestmentPrice($this->testCase->getGameEvents(), $investmentId);
        $currentInvestmentPriceFormatted = $this->numberFormatMoney($currentInvestmentPrice->value);
        $investedMoney = $amount * $currentInvestmentPrice->value;
        $dividende = $investmentId === InvestmentId::MERFEDES_PENZ
            ? KonjunkturphaseState::getCurrentKonjunkturphase($this->testCase->getGameEvents())->getDividend()->format()
            : "/";

        // get players balance before action
        $playersBalanceBeforeAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();

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
        $playersBalanceAfterAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();
        Assert::assertEquals(
            $playersBalanceAfterAction,
            $playersBalanceBeforeAction - $investedMoney,
            "Balance has been changed by investment"
        );

        return $this;
    }

    public function sellStocksThatOtherPlayerIsBuying(InvestmentId $stockId): void {
        $lastInvestmentBoughtByAPlayer = $this->testCase->getGameEvents()->findLast(PlayerHasBoughtInvestment::class);
        $nameOfPlayerWhoBoughtInvestment = PlayerState::getNameForPlayer(
            $this->testCase->getGameEvents(),
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
            class=\"button button--type-outline-primary $this->playerColorClass\"
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
//    class=\"button button--type-primary $this->playerColorClass\"
//    disabled wire:dirty.remove.attr=\"disabled\"
//>
//    Anteile verkaufen
//</button>",

    }

    public function doMinijob(): static {

        // get top card from Minijob
        $topCardMinijob = $this->getTopCardFromCategory(CategoryId::MINIJOBS);
        $topCardMinijobTitle = $topCardMinijob->getTitle();
        $formattedGuthabenChange = $topCardMinijob->getResourceChanges()->guthabenChange->formatWithIcon();

        dump($topCardMinijob);

        // get players available Zeitsteine
        $availableZeitsteine = $this->getAvailableZeitsteine();
        // get players remaining Zeitsteine before action
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        // check that Zeitsteine are rendered correctly
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        // get available Slots for categories

        // get available Slots for Kompetenzen

        // get players balance before action
        $playersBalanceBeforeAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();

        $this->testableGameUi
            // check that message is not in Ereignisprotokoll
            ->assertDontSeeHtml([
                "<!--[if BLOCK]><![endif]-->            <li class=\"event-log__entry\">
                <!--[if BLOCK]><![endif]-->                    <strong class=\"event-log__entry-player-name $this->playerColorClass\">$this->playerName</strong>
                <!--[if ENDBLOCK]><![endif]-->
                <span class=\"event-log__entry-text\">
                    macht Minijob &#039;$topCardMinijobTitle&#039;
                </span>
                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]-->                    <div class=\"resource-changes resource-changes--horizontal\">
    <span class=\"sr-only\">Du bekommst/verlierst: </span>
    <!--[if BLOCK]><![endif]-->        <div class=\"resource-change\">$formattedGuthabenChange</div>"
            ])
            // play card
            ->call('doMinijob')
            ->assertSeeHtml([])
            ->call('closeMinijob')
            // check that message is now logged
            ->assertSeeHtml([
                "<!--[if BLOCK]><![endif]-->            <li class=\"event-log__entry\">
                <!--[if BLOCK]><![endif]-->                    <strong class=\"event-log__entry-player-name $this->playerColorClass\">$this->playerName</strong>
                <!--[if ENDBLOCK]><![endif]-->
                <span class=\"event-log__entry-text\">
                    macht Minijob &#039;$topCardMinijobTitle&#039;
                </span>
                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]-->                    <div class=\"resource-changes resource-changes--horizontal\">
    <span class=\"sr-only\">Du bekommst/verlierst: </span>
    <!--[if BLOCK]><![endif]-->        <div class=\"resource-change\">$formattedGuthabenChange</div>"
            ]);

        return $this;
    }

}


// Geld bekommen
//
//  <div class=\"resource-changes resource-changes--horizontal\">
//      <span class=\"sr-only\">Du bekommst/verlierst: </span>
//      <div class="resource-change">
//          <span class='text--currency'>
//              <i aria-hidden='true' class='text--success icon-plus'></i>
//              <span class='sr-only'>+</span>
//              " 3.000,00 "
//              <i aria-hidden='true' class='icon-euro'></i>
//              <span class='sr-only'>€</span>
//          </span>
//      </div>
//  </div>

// Geld verlieren
//
//  <div class=\"resource-changes resource-changes--horizontal\">
//      <span class=\"sr-only\">Du bekommst/verlierst: </span>
//      <div class="resource-change">
//          <span class='text--currency'>
//              <i aria-hidden='true' class='text--danger icon-minus'></i>
//              <span class='sr-only'>-</span>
//              " 3.000,00 "
//              <i aria-hidden='true' class='icon-euro'></i>
//              <span class='sr-only'>€</span>
//          </span>
//      </div>
//  </div>
