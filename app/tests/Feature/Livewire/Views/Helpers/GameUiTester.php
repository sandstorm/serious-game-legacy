<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\State\LogState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Insurance\InsuranceDefinition;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Investments\InvestmentFinder;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

readonly class GameUiTester {

    public Testable $testableGameUi;
    public readonly array $categoryIds;
    private string $playerColorClass;

    public function __construct(private TestCase $testCase, private PlayerId $playerId, private string $playerName) {
        $this->testableGameUi = Livewire::test(GameUi::class, [
            'gameId' => $this->testCase->gameId,
            'myself' => $this->playerId
        ]);
        $this->playerColorClass = PlayerState::getPlayerColorClass($this->testCase->getGameEvents(), $this->playerId);
        $this->categoryIds = [
            CategoryId::BILDUNG_UND_KARRIERE,
            CategoryId::SOZIALES_UND_FREIZEIT,
            CategoryId::JOBS,
            CategoryId::INVESTITIONEN
        ];
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

        return $this;
    }

    public function checkThatSidebarActionsAreVisible(bool $actionsAreVisible): static {
        if ($actionsAreVisible) {
            $this->testableGameUi
                ->assertSeeHtml([
                    'wire:click="showTakeOutALoan()"',
                    '<span>Kredit aufnehmen</span>',
                    "wire:click=\"showExpensesTab('insurances')\"",
                    '<span>Versicherung abschließen</span>',
                    'wire:click="spielzugAbschliessen()"',
                    'Spielzug beenden',
                ]);
        } else {
            $this->testableGameUi
                ->assertDontSeeHtml([
                    'wire:click="showTakeOutALoan()"',
                    '<span>Kredit aufnehmen</span>',
                    "wire:click=\"showExpensesTab('insurances')\"",
                    '<span>Versicherung abschließen</span>',
                    'wire:click="spielzugAbschliessen()"',
                    'Spielzug beenden',
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

        $availableZeitsteine = $this->getAvailableZeitsteine();
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        $availableCategorySlots = $this->getAvailableCategorySlots($this->categoryIds);
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

        $availableKompetenzSlots = $this->getAvailableKompetenzSlots();
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots);

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

        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 1 + $topCardZeitstein,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used in corresponding category
        $this->compareUsedSlots($categoryId->name, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

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

    /**
     * @param CategoryId $categoryId
     * @return KategorieCardDefinition | JobCardDefinition | MinijobCardDefinition | WeiterbildungCardDefinition
     */
    private function getTopCardFromCategory(CategoryId $categoryId): KategorieCardDefinition|JobCardDefinition|MinijobCardDefinition|WeiterbildungCardDefinition {
        $lebenszielPhaseId = PlayerState::getCurrentLebenszielphaseIdForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        );

        if ($categoryId === CategoryId::MINIJOBS || $categoryId === CategoryId::WEITERBILDUNG) {
            $pileId = new PileId($categoryId);
        } else {
            $pileId = new PileId($categoryId, $lebenszielPhaseId);
        }

        $topCardIdOnPile = PileState::topCardIdForPile($this->testCase->getGameEvents(), $pileId);

        return CardFinder::getInstance()->getCardById(new CardId($topCardIdOnPile->value));
    }

    public function getAvailableZeitsteine(): int {
        return $this->testCase
            ->getKonjunkturphaseDefinition()
            ->zeitsteine
            ->getAmountOfZeitsteineForPlayer(count($this->testCase->players));
    }

    public function getPlayersZeitsteine(): int {
        return PlayerState::getResourcesForPlayer($this->testCase->getGameEvents(), $this->playerId)->zeitsteineChange;
    }

    public function assertVisibilityOfZeitsteine($currentZeitsteine, $availableZeitsteine): static {
        $this->testableGameUi->assertSeeHtml(
            $this->playerName . ' hat noch ' . $currentZeitsteine . ' von ' . $availableZeitsteine . ' Zeitsteinen übrig.'
        );
        return $this;
    }

    /**
     * @param CategoryId[] $categoryIds
     * @return array
     */
    public function getAvailableCategorySlots(array $categoryIds): array {
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
    public function getOccupiedCategorySlots(array $categoryIds): array {
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

    public function assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlots): static {
        foreach ($availableCategorySlots as $categoryName => $availableSlots) {
            $slotsUsedByPlayer = [];
            foreach ($usedCategorySlots[$categoryName]['players'] as $playerName => $amount) {
                $slotsUsedByPlayer[] = $playerName . ': ' . $amount;
            }
            $this->testableGameUi->assertSeeHtml(
                $usedCategorySlots[$categoryName]['total'] . ' von ' . $availableSlots . ' Zeitsteinen wurden platziert. ' . implode(', ', $slotsUsedByPlayer)
            );
        }
        return $this;
    }

    public function getAvailableKompetenzSlots(): array {
        $currentLebenszielphaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        );
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $currentLebenszielphaseDefinition->bildungsKompetenzSlots,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $currentLebenszielphaseDefinition->freizeitKompetenzSlots
        ];
    }

    public function getPlayersKompetenzsteine(): array {
        $playerResources = PlayerState::getResourcesForPlayer($this->testCase->getGameEvents(), $this->playerId);
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $playerResources->bildungKompetenzsteinChange,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $playerResources->freizeitKompetenzsteinChange
        ];
    }

    public function assertVisibilityOfKompetenzen($playersKompetenzsteine, $availableKompetenzSlots): static {
        foreach ($playersKompetenzsteine as $categoryName => $achievedAmount) {
            // replace '&' with '&amp;'
            $categoryNameAdapted = str_replace("&", "&amp;", $categoryName);
            $availableAmount = $availableKompetenzSlots[$categoryName];
            $this->testableGameUi->assertSeeHtml(
                "Deine Kompetenzsteine im Bereich $categoryNameAdapted: $achievedAmount von $availableAmount"
            );
        }
        return $this;
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

    private function assertVisibilityOfBalance(): void {
        $playersGuthabenFormatted = PlayerState::getGuthabenForPlayer($this->testCase->getGameEvents(), $this->playerId)->format();

        $this->testableGameUi->assertSeeHtml(
            "<button title=\"Moneysheet öffnen\" class=\"button button--type-primary $this->playerColorClass\" wire:click=\"showMoneySheet()\">
                        $playersGuthabenFormatted"
        );
    }

    public function compareUsedSlots($categoryName, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction): static {
        foreach ($usedCategorySlotsAfterAction as $name => $usedSlots) {
            if ($name === $categoryName) {
                Assert::assertEquals(
                    $usedSlots['total'] - 1,
                    $usedCategorySlotsBeforeAction[$name]['total'],
                    "Zeitsteinslots in $name have been increased by 1"
                );
            } else {
                Assert::assertEquals(
                    $usedSlots['total'],
                    $usedCategorySlotsBeforeAction[$name]['total'],
                    "Zeitsteinslots in $name have not changed"
                );
            }
        }
        return $this;
    }

    private function getTopCardKompetenzsteinChange(KategorieCardDefinition $topCard): array {
        return [
            CategoryId::BILDUNG_UND_KARRIERE->value => $topCard->getResourceChanges()->bildungKompetenzsteinChange,
            CategoryId::SOZIALES_UND_FREIZEIT->value => $topCard->getResourceChanges()->freizeitKompetenzsteinChange
        ];
    }

    private function compareKompetenzsteine(
        $kompetenzsteinChange,
        $playersKompetenzsteineBeforeAction,
        $playersKompetenzsteineAfterAction
    ): void {
        foreach ($playersKompetenzsteineAfterAction as $categoryName => $amount) {
            Assert::assertEquals(
                $playersKompetenzsteineBeforeAction[$categoryName] + $kompetenzsteinChange[$categoryName],
                $playersKompetenzsteineAfterAction[$categoryName],
                'Kompetenzsteine have been changed by values displayed on card'
            );
        }
    }

    public function tryToPlayCardWhenItIsNotThePlayersTurn(CategoryId $categoryId): void {
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
        $modalAttributes = [
            'hasIcon' => true,
            'hasTitle' => false,
            'hasFooter' => true
        ];
        $additionalModalContent = [
            '<h3>Du bist am Zug!</h3>',
            'wire:click="startSpielzug()',
            'Ok'
        ];
        // check that modal is visible
        $this->assertSeeMandatoryModal($modalAttributes, $additionalModalContent);

        // player confirms starting turn
        $this->testableGameUi
            ->call('startSpielzug');

        // check that modal is not visible anymore
        $this->assertDoNotSeeMandatoryModal($additionalModalContent);

        return $this;
    }

    private function assertSeeMandatoryModal(array $modalAttributes, array $additionalContent): void {
        $this->testableGameUi
            ->assertSeeHtml([
                '<div class="modal__backdrop"></div>',
                '<div class="modal__content">',
                '<div class="modal__body" id="mandatory-modal-content">',
                ...$additionalContent
            ])
            ->assertDontSeeHtml('<div class="modal__close-button">');

        if ($modalAttributes['hasIcon']) {
            $this->testableGameUi->assertSeeHtml('<div class="modal__icon">');
        }

        if ($modalAttributes['hasTitle']) {
            $this->testableGameUi->assertSeeHtml('<h2 class="modal__header" id="mandatory-modal-headline">');
        }

        if ($modalAttributes['hasFooter']) {
            $this->testableGameUi->assertSeeHtml('<footer class="modal__actions">');
        }
    }

    private function assertDoNotSeeMandatoryModal(array $additionalContent): void {
        $this->testableGameUi->assertDontSeeHtml([
            '<div class="modal__backdrop"></div>',
            '<div class="modal__content">',
            '<div class="modal__body" id="mandatory-modal-content">',
            ...$additionalContent
        ]);
    }

    public function seeUpdatedGameboard(): static {
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

        return $this;
    }

    public function finishTurn(): static {
        $this->testableGameUi->call('spielzugAbschliessen');
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

        $availableZeitsteine = $this->getAvailableZeitsteine();
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        $availableCategorySlots = $this->getAvailableCategorySlots($this->categoryIds);
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

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

        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 2,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used for category JOBS
        $this->compareUsedSlots(
            CategoryId::JOBS->name,
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
            ->call('toggleInvestitionenSelectionModal');

        $modalAttributes = [
            'isCloseable' => true,
            'closeModalFunction' => "toggleInvestitionenSelectionModal()",
            'hasTitle' => true,
            'hasFooter' => false
        ];
        $additionalModalContent = [
            '<div class="investitionen-overview">',
            '<button class="card" wire:click="toggleStocksModal()">',
            '<h4 class="card__title">Aktien</h4>',
            '<button class="card" wire:click="toggleETFModal()">',
            '<h4 class="card__title">ETF</h4>',
            '<button class="card" wire:click="toggleCryptoModal()">',
            '<h4 class="card__title">Krypto</h4>',
            '<button class="card" wire:click="toggleImmobilienModal()">',
            '<h4 class="card__title">Immobilien</h4>'
        ];

        $this->assertSeeModal($modalAttributes, $additionalModalContent);

        return $this;
    }

    private function assertSeeModal(array $modalAttributes, array $additionalContent): void {
        $this->testableGameUi->assertSeeHtml([
            '<div class="modal__content">',
            '<div class="modal__body" id="modal-content">',
            ...$additionalContent
        ]);

        if ($modalAttributes['isCloseable']) {
            $closeModalFunction = $modalAttributes['closeModalFunction'];
            $this->testableGameUi->assertSeeHtml([
                "<div class=\"modal__backdrop\" wire:click=$closeModalFunction></div>",
                '<div class="modal__close-button">'
            ]);
        } else {
            $this->testableGameUi->assertSeeHtml([
                '<div class="modal__backdrop"></div>'
            ]);
        }

        if ($modalAttributes['hasTitle']) {
            $this->testableGameUi->assertSeeHtml('<h2 class="modal__header" id="modal-headline">');
        }

        if ($modalAttributes['hasFooter']) {
            $this->testableGameUi->assertSeeHtml('<footer class="modal__actions">');
        }
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
                'kaufen',
                'verkaufen'
            ]);

        $modalAttributes = [
            'isCloseable' => true,
            'closeModalFunction' => "toggleStocksModal()",
            'hasTitle' => true,
            'hasFooter' => false
        ];
        $additionalModalContent = [
            "<h4>$firstStock->value</h4>",
            InvestmentPriceState::getCurrentInvestmentPrice($this->testCase->getGameEvents(), $firstStock)->format(),
            "<h4>$secondStock->value</h4>",
            InvestmentPriceState::getCurrentInvestmentPrice($this->testCase->getGameEvents(), $secondStock)->format()
        ];
        $this->assertSeeModal($modalAttributes, $additionalModalContent);

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
            ]);

        $modalAttributes = [
            'isCloseable' => true,
            'closeModalFunction' => "toggleStocksModal()",
            'hasTitle' => true,
            'hasFooter' => false
        ];
        $additionalModalContent = [
            "Kauf - $investmentId->value",
            $currentInvestmentPrice->format(),
            "<strong>$investmentDefinition->longTermTrend%</strong>",
            "<strong>$investmentDefinition->fluctuations%</strong>",
            "<strong>$dividende</strong>"
        ];
        $this->assertSeeModal($modalAttributes, $additionalModalContent);

        $this->testableGameUi
            // set amount
            ->set('buyInvestmentsForm.amount', $amount)
            // buy stocks
            ->call('buyInvestments', $investmentId->value)
            ->assertSee(
                "Investiert in '$investmentId->value' und kauft $amount Anteile zum Preis von $currentInvestmentPriceFormatted €"
            );

        // check that modal is no longer visible
        $this->assertDoNotSeeModal($modalAttributes, $additionalModalContent);

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

    private function assertDoNotSeeModal(array $modalAttributes, array $additionalContent): void {
        $this->testableGameUi->assertDontSeeHtml([
            '<div class="modal__content">',
            '<div class="modal__body" id="modal-content">',
            ...$additionalContent
        ]);

        if ($modalAttributes['isCloseable']) {
            $closeModalFunction = $modalAttributes['closeModalFunction'];
            $this->testableGameUi->assertDontSeeHtml([
                "<div class=\"modal__backdrop\" wire:click=$closeModalFunction></div>",
                '<div class="modal__close-button">',
            ]);
        }

        if ($modalAttributes['hasTitle']) {
            $this->testableGameUi->assertDontSeeHtml('<h2 class="modal__header" id="modal-headline">');
        }
    }

    public function sellStocksThatOtherPlayerIsBuying(InvestmentId $stockId): void {
        $lastInvestmentBoughtByAPlayer = $this->testCase->getGameEvents()->findLast(PlayerHasBoughtInvestment::class);
        $nameOfPlayerWhoBoughtInvestment = PlayerState::getNameForPlayer(
            $this->testCase->getGameEvents(),
            $lastInvestmentBoughtByAPlayer->playerId
        );

        $modalAttributes = [
            'hasIcon' => true,
            'hasTitle' => true,
            'hasFooter' => true
        ];
        $additionalModalContent = [
            "Verkauf - $stockId->value <i class=\"icon-aktien\" aria-hidden=\"true\"></i>",
            "<h4>$nameOfPlayerWhoBoughtInvestment hat in $stockId->value investiert!</h4>",
            "Ich möchte nichts verkaufen"
        ];
        $this->assertSeeMandatoryModal($modalAttributes, $additionalModalContent);

//        Todo: überprüfen, ob Player Aktien besitzt --> abhängig davon wird anderer Text gerendert (siehe unten)
        $this->testableGameUi
            ->assertSeeHtml(
                "Du hast keine Anteile vom Typ $stockId->value."
            );

        $this->testableGameUi
            ->call('closeSellInvestmentsModal');

        $this->assertDoNotSeeMandatoryModal($additionalModalContent);

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

    public function doWeiterbildungWithSuccess(): static {
        $topCard = $this->getTopCardFromCategory(CategoryId::WEITERBILDUNG);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardDescription = $topCard->getDescription();
        $topCardAnswerOptions = $topCard->getAnswerOptions();

        [$answerOptionsDescription, $rightAnswerOption] = $this->getArrayForTestingAnswerOptionsFromWeiterbildung($topCardAnswerOptions);

        $availableZeitsteine = $this->getAvailableZeitsteine();
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        $availableCategorySlots = $this->getAvailableCategorySlots($this->categoryIds);
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

        $availableKompetenzSlots = $this->getAvailableKompetenzSlots();
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots);

        $playersBalanceBeforeAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();

        $this->testableGameUi
            ->call('showWeiterbildung')
            ->assertSeeHtml([
                // check that message is logged in Ereignisprotokoll
                "<span class=\"event-log__entry-text\">
                    macht eine Weiterbildung
                </span>",
                "<span class=\"sr-only\">-1 Zeitsteine </span",
            ]);

        // check that modal is visible
        $modalAttributes = [
            'isCloseable' => false,
            'hasTitle' => true,
            'hasFooter' => false
        ];
        $additionalModalContent = [
            "<div class=\"weiterbildung__header-category\">Weiterbildung</div>",
            $topCardTitle,
            $topCardDescription,
            ...$answerOptionsDescription,
            "<div class=\"weiterbildung__footer\">
                <div class=\"weiterbildung__footer-icon\">
                    <i class=\"icon-plus\" aria-hidden=\"true\"></i>
                    <div class=\"kompetenz-icon \">",
            "Auswahl bestätigen"
        ];
        $this->assertSeeModal($modalAttributes, $additionalModalContent);

        $this->testableGameUi
            // log answer
            ->set('weiterbildungForm.answer', $rightAnswerOption)
            // submit answer
            ->call('submitAnswerForWeiterbildung')
            // show result of answer
            ->assertSee('Super, richtig gelöst!')
            // check that button has changed
            ->assertSeeHtml("Weiter")
            ->call('closeWeiterbildung')
            // check that result of Weiterbildung is logged in Ereignisprotokoll
            ->assertSeeHtml([
                "<span class=\"event-log__entry-text\">
                    hat die Weiterbildung richtig beantwortet
                </span>",
                "<span class=\"sr-only\">Du bekommst/verlierst: </span>",
                "<span class=\"sr-only\">0.5 Bildung &amp; Karriere Kompetenzsteine </span>"
            ]);

        // check that modal is no longer visible
        $this->assertDoNotSeeModal($modalAttributes, $additionalModalContent);

        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 1,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that 1 Zeitsteinslot is used for Weiterbildung
        $this->compareUsedSlots(CategoryId::WEITERBILDUNG->name, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineAfterAction, $availableKompetenzSlots);
        // check that player got half Kompetenzstein for Weiterbildung
        $this->compareKompetenzsteine(
            [CategoryId::BILDUNG_UND_KARRIERE->value => 0.5, CategoryId::SOZIALES_UND_FREIZEIT->value => 0],
            $playersKompetenzsteineBeforeAction,
            $playersKompetenzsteineAfterAction
        );

        // check that players Guthaben has not changed
        $playersBalanceAfterAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();
        Assert::assertEquals(
            $playersBalanceAfterAction,
            $playersBalanceBeforeAction,
            "Balance has not changed"
        );

        return $this;
    }

    private function getArrayForTestingAnswerOptionsFromWeiterbildung($topCardAnswerOptions): array {
        $answerOptionsDescription = [];
        $rightAnswerOption = "";

        foreach ($topCardAnswerOptions as $index => $answerOption) {
            $answerOptionsDescription[$index] = $answerOption->text;
            if ($answerOption->isCorrect) {
                $rightAnswerOption = $answerOption->id->value;
            }
        }

        return [$answerOptionsDescription, $rightAnswerOption];
    }

    public function doMinijob(): static {
        $topCard = $this->getTopCardFromCategory(CategoryId::MINIJOBS);

        // get properties from top card
        $topCardTitle = $topCard->getTitle();
        $topCardDescription = $topCard->getDescription();
        $topCardZeitstein = $topCard->getResourceChanges()->zeitsteineChange;
        $topCardGuthabenChange = $topCard->getResourceChanges()->guthabenChange->value;
        $formattedGuthabenChange = $topCard->getResourceChanges()->guthabenChange->formatWithIcon();

        $availableZeitsteine = $this->getAvailableZeitsteine();
        $playersZeitsteineBeforeAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineBeforeAction, $availableZeitsteine);

        $availableCategorySlots = $this->getAvailableCategorySlots($this->categoryIds);
        $usedCategorySlotsBeforeAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsBeforeAction);

        $availableKompetenzSlots = $this->getAvailableKompetenzSlots();
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots);

        $playersBalanceBeforeAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();

        // get LogEntries before action
        $amountLogEntriesBeforeAction = count(LogState::getLogEntries($this->testCase->getGameEvents()));

        $this->testableGameUi
            // check that message is not in Ereignisprotokoll
            ->assertDontSeeHtml([
                "macht Minijob &#039;$topCardTitle&#039;",
            ])
            // do Minijob
            ->call('doMinijob');

        $modalAttributes = [
            'isCloseable' => true,
            'closeModalFunction' => "closeMinijob()",
            'hasTitle' => true,
            'hasFooter' => false
        ];
        $additionalModalContent = [
            $topCardTitle,
            'Minijob',
            $topCardDescription,
            '<span class="sr-only">Du bekommst/verlierst: </span>',
            $formattedGuthabenChange,
            '<span class="sr-only">-1 Zeitsteine </span>',
            'Akzeptieren'
        ];
        $this->assertSeeModal($modalAttributes, $additionalModalContent);

        $this->testableGameUi
            ->call('closeMinijob')
            // check that message is now logged
            ->assertSeeHtml([
                "macht Minijob &#039;$topCardTitle&#039;",
                "<div class=\"resource-change\">$formattedGuthabenChange</div>",
            ]);

        // get LogEntries after action
        $amountLogEntriesAfterAction = count(LogState::getLogEntries($this->testCase->getGameEvents()));
        // check that LogEntries have increased by 1
        Assert::assertEquals(
            $amountLogEntriesAfterAction - 1,
            $amountLogEntriesBeforeAction,
            'LogEntries have increased by 1'
        );

        $playersZeitsteineAfterAction = $this->getPlayersZeitsteine();
        $this->assertVisibilityOfZeitsteine($playersZeitsteineAfterAction, $availableZeitsteine);
        // check that player has used Zeitsteine
        Assert::assertEquals(
            $playersZeitsteineBeforeAction - 1 + $topCardZeitstein,
            $playersZeitsteineAfterAction,
            'Zeitsteine have been reduced'
        );

        $usedCategorySlotsAfterAction = $this->getOccupiedCategorySlots($this->categoryIds);
        $this->assertVisibilityOfCategorySlots($availableCategorySlots, $usedCategorySlotsAfterAction);
        // check that Zeitsteinslots remain the same
        $this->compareUsedSlots(CategoryId::MINIJOBS->name, $usedCategorySlotsBeforeAction, $usedCategorySlotsAfterAction);

        $playersKompetenzsteineAfterAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineAfterAction, $availableKompetenzSlots);
        // check that players Kompetenzsteine remain the same
        $this->compareKompetenzsteine(
            [CategoryId::BILDUNG_UND_KARRIERE->value => 0, CategoryId::SOZIALES_UND_FREIZEIT->value => 0],
            $playersKompetenzsteineBeforeAction,
            $playersKompetenzsteineAfterAction
        );

        // check that player got Guthaben from Minijob
        $playersBalanceAfterAction = $this->getPlayersBalance();
        $this->assertVisibilityOfBalance();
        Assert::assertEquals(
            $playersBalanceBeforeAction + $topCardGuthabenChange,
            $playersBalanceAfterAction,
            "Balance has been changed by $topCardGuthabenChange"
        );

        return $this;
    }

    public function openMoneySheetInsurance(): static {
        $this->testableGameUi->call('showExpensesTab', 'insurances');
        return $this;
    }

    public function seeMoneySheetInsurance(): static {
        $allInsurances = InsuranceFinder::getInstance()->getAllInsurances();
        [$insuranceNames, $insuranceCosts] = $this->getArrayForTestingVisibleValuesOnInsuranceSheet($allInsurances);

        $this->testableGameUi->assertSee([
            'Kredite',
            'Versicherungen',
            'Steuern und Abgaben',
            'Lebenshaltungskosten',
            ...$insuranceNames,
            ...$insuranceCosts,
            'Summe Versicherungen',
            'Änderungen speichern'
        ]);

        return $this;
    }

    /**
     * @param InsuranceDefinition[] $allInsurances
     */
    private function getArrayForTestingVisibleValuesOnInsuranceSheet(array $allInsurances): array {
        $lebenszielPhase = PlayerState::getCurrentLebenszielphaseIdForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        )->value;

        $insuranceNames = [];
        $insuranceCosts = [];

        foreach ($allInsurances as $index => $insurance) {
            $insuranceNames[$index] = $insurance->type->value;
            $insuranceCosts[$index] = $this->numberFormatMoney($insurance->annualCost[$lebenszielPhase]->value) . " €";
        }

        return [$insuranceNames, $insuranceCosts];
    }

    public function seeAnnualInsurancesCost(): static {
        $allInsurances = InsuranceFinder::getInstance()->getAllInsurances();
        $lebenszielPhase = PlayerState::getCurrentLebenszielphaseIdForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        )->value;
        $annualInsurancesCost = 0;

        foreach ($allInsurances as $insurance) {
            $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance(
                $this->testCase->getGameEvents(),
                $this->playerId,
                $insurance->id
            );
            if ($hasInsurance) {
                $annualInsurancesCost += $insurance->annualCost[$lebenszielPhase]->value;
            }
        }

        $this->testableGameUi
            ->assertSee($this->numberFormatMoney($annualInsurancesCost));

        return $this;
    }

    public function changeInsurance(array $insurancesToChange): static {
        foreach ($insurancesToChange as $changedInsurance) {
            $insuranceDefinition = InsuranceFinder::getInstance()->findInsuranceByType($changedInsurance['type']);
            $insuranceIdValue = $insuranceDefinition->id->value;
            $changedTo = $changedInsurance['changeTo'];

            $this->testableGameUi
                ->set("moneySheetInsurancesForm.insurances.$insuranceIdValue.value", $changedTo);
        }

        return $this;
    }

    public function confirmInsuranceChoice(): static {
        $this->testableGameUi
            ->call('setInsurances');

        return $this;
    }

    public function seeInsuranceChangeInEreignisprotokoll($insurancesToChange): static {
        foreach ($insurancesToChange as $changedInsurance) {
            $insuranceValue = $changedInsurance['type']->value;
            $loggedMessage = $changedInsurance['changeTo']
                ? "schließt '$insuranceValue' ab"
                : "kündigt '$insuranceValue'";
            $this->testableGameUi->assertSee($loggedMessage);
        }

        return $this;
    }

    public function closeMoneySheet(): static {
        $this->testableGameUi->call('closeMoneySheet');
        return $this;
    }

    public function openLebenszielModal(): static {
        $this->testableGameUi->call('showPlayerLebensziel', $this->playerId);
        return $this;
    }

    public function seeLebenszielModal(): static {
        $playersGuthaben = PlayerState::getGuthabenForPlayer($this->testCase->getGameEvents(), $this->playerId)->value;
        $lebenszielDefinitionForPlayer = PlayerState::getLebenszielDefinitionForPlayer(
            $this->testCase->getGameEvents(),
            $this->playerId
        );
        $lebenszielPhaseDefinitions = $lebenszielDefinitionForPlayer->phaseDefinitions;

        foreach ($lebenszielPhaseDefinitions as $lebenszielPhaseDefinition) {
            $moneyAmountChangeLebenszielphase = $lebenszielPhaseDefinition->investitionen->value;
            $this->testableGameUi->assertSee(
                $lebenszielPhaseDefinition->description,
                $this->numberFormatMoney($moneyAmountChangeLebenszielphase)
            );
        }

        $this->testableGameUi->assertSee([
            'Dein Lebensziel',
            $lebenszielDefinitionForPlayer->name,
            'Phase 1',
            'Phase 2',
            'Phase 3',
            'Phasenwechsel',
            'Kontostand',
            $this->numberFormatMoney($playersGuthaben)
        ]);

        $availableKompetenzSlots = $this->getAvailableKompetenzSlots();
        $playersKompetenzsteineBeforeAction = $this->getPlayersKompetenzsteine();
        $this->assertVisibilityOfKompetenzen($playersKompetenzsteineBeforeAction, $availableKompetenzSlots);

        return $this;
    }

    public function assertDoNotSeeMessage($message): static {
        $this->testableGameUi->assertDontSeeHtml($message);
        return $this;
    }

    public function assertSeeMessage($message): static {
        $this->testableGameUi->assertSee($message);
        return $this;
    }

    public function closeMessage(): static {
        $this->testableGameUi->call('closeNotification');
        return $this;
    }

}
