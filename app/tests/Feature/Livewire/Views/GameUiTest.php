<?php

namespace Tests\Feature\Livewire;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Livewire\Views\Helpers\GameUiTester;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
    $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);
});

describe('GameUi', function () {
    it('renders GameUi', function () {
        /** @var TestCase $this */
        $gameUiTester = new GameUiTester($this->gameId, $this->players[0], 'Player 0');
        $gameUiTester->testableGameUi->assertStatus(200);
        $gameUiTester->startGame($this)->checkThatSidebarActionsAreVisible(true, $this);
    });

    it('plays a card and finishes turn', function (CategoryId $categoryId) {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($this->gameId, $this->players[0], 'Player 0')
            ->startGame($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->drawAndPlayCard($testCase, $categoryId)
            ->finishTurn()
            // check that player can not play a card after finishing turn
            ->tryToPlayCardWhenItIsNotThePlayersTurn($testCase, $categoryId);

        // check that opponent player receives a message that it is their turn
        new GameUiTester($this->gameId, $this->players[1], 'Player 1')
            ->startGame($testCase)
            ->startTurn($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase);
    })->with([CategoryId::BILDUNG_UND_KARRIERE, CategoryId::SOZIALES_UND_FREIZEIT]);

    it('gets enough Kompetenzsteine and accepts a job offer', function () {
        /** @var TestCase $this */
        $testCase = $this;

        // first player plays first card for Bildung & Karriere and finishes turn
        new GameUiTester($this->gameId, $this->players[0], 'Player 0')
            ->startGame($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->drawAndPlayCard($testCase, CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn();

        // second player uses 1 Zeitstein and finishes turn
        new GameUiTester($this->gameId, $this->players[1], 'Player 1')
            ->startGame($testCase)
            ->startTurn($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->drawAndPlayCard($testCase, CategoryId::SOZIALES_UND_FREIZEIT)
            ->finishTurn();

        // first player plays second card for Bildung & Karriere and finishes turn
        new GameUiTester($this->gameId, $this->players[0], 'Player 0')
            ->startTurn($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->drawAndPlayCard($testCase, CategoryId::BILDUNG_UND_KARRIERE)
            ->finishTurn();

        // second player uses 1 Zeitstein and finishes turn
        new GameUiTester($this->gameId, $this->players[1], 'Player 1')
            ->startTurn($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->drawAndPlayCard($testCase, CategoryId::SOZIALES_UND_FREIZEIT)
            ->finishTurn();

        // first player accepts a job and finishes turn
        new GameUiTester($this->gameId, $this->players[0], 'Player 0')
            ->startTurn($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->openJobBoard($testCase)
            ->acceptJobWhenPlayerCurrentlyHasNoJob($testCase)
            ->finishTurn();

        // check that opponent player receives a message that it is their turn
        new GameUiTester($this->gameId, $this->players[1], 'Player 1')
            ->startTurn($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase);
    });

    it('invests in Aktien', function () {
        /** @var TestCase $this */
        $testCase = $this;

        new GameUiTester($this->gameId, $this->players[0], 'Player 0')
            ->startGame($testCase)
            ->checkThatSidebarActionsAreVisible(true, $testCase)
            ->openInvestmentsOverview()
            ->chooseStocks($testCase)
            ->buyStocks($testCase, InvestmentId::BETA_PEAR, 10);

        // opponent player has possibility to sell stocks
        new GameUiTester($this->gameId, $this->players[1], 'Player 1')
            ->startGame($testCase)
            ->checkThatSidebarActionsAreVisible(false, $testCase);

        /*
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])

            // player chooses Investments
            ->call('toggleInvestitionenSelectionModal')
            ->assertSee(['Investitionen', 'Aktien', 'ETF', 'Krypto', 'Immobilien'])

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

            ->call('showBuyInvestmentOfType', 'BetaPear')
            ->assertSee([
                'Kauf - BetaPear',
                'Ein junges, ambitioniertes Tech-Unternehmen mit Fokus auf Nachhaltigkeit, das auf die nächste große Innovation setzt. Die Aktie bietet hohe, aber stark schwankende Kurschancen und zahlt keine Dividenden.',
                'Stückzahl',
                'Summe Kauf',
                '50,00 €',
                '0,00 €',
                'Langfristige Tendenz:',
                '9%',
                'Kursschwankungen:',
                '40%',
                'Dividende pro Aktie:'
            ])
            ->set("buyInvestmentsForm.amount", '456')
            ->call('buyInvestments', 'BetaPear')
            ->assertSee("Investiert in 'BetaPear' und kauft 456 Anteile zum Preis von 50,00 €");

        //////////////////////////////////////////////////////////////////////////////

        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            // opponent player has possibility to sell stocks
            ->assertSee([
                'Verkauf - BetaPear',
                'Player 0 hat in BetaPear investiert!',
                'Du hast keine Anteile vom Typ BetaPear.',
                'Ich möchte nichts verkaufen'
            ])
            ->call('closeSellInvestmentsModal');

        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that second player receives a message that it is their turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->assertSee('Du bist am Zug');
        */
    });

    it('does a Weiterbildung', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->call('showWeiterbildung')
            ->assertSee([
                'Quiz',
                'Weiterbildung',
                'Ich mache eine Weiterbildung. Warum machst du die Weiterbildung?',
                'Tarifliche Entlohnung und Arbeitsplatzsicherheit',
                'Angemessene Vergütung und soziale Absicherung',
                'Maximale Kosteneffizienz und unternehmerische Flexibilität',
                'Karriereförderung und Mitbestimmungsmöglichkeiten',
                'Auswahl bestätigen',
            ]);
        // ToDo: Frage beantworten

//            ->set('weiterbildungsForm');
//            ->call('submitAnswerForWeiterbildung')
//            ->assertSee('Super, richtig gelöst!');

//        Schade, das war nicht die richtige Antwort!

//        Livewire::test(HasWeiterbildung::class)->set('weiterbildungsForm', 'c');
    });

    it('does a Minijob', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            // check that message is not in Ereignisprotokoll
            ->assertDontSee(["macht Minijob 'Minijob", '5.000,00'])
            // check that player has all of their Zeitsteine
            ->assertSeeHtml('Player 0 hat noch 6 von 6 Zeitsteinen übrig.')
            ->call('doMinijob')
            ->assertSee([
                'Minijob',
                'Kellnerin im Ausland. Einmalzahlung 5.000 €.',
                '5.000,00',
                'Akzeptieren'
            ])
            ->call('closeMinijob')
            // check that message is now logged
            ->assertSee(["macht Minijob 'Minijob", '5.000,00'])
            // check that player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 5 von 6 Zeitsteinen übrig.')
            // check that player can not draw card
            ->call('activateCard', 'Bildung & Karriere')
            ->assertSee('Du hast bereits eine andere Aktion ausgeführt')
            ->call('activateCard', 'Freizeit & Soziales')
            ->assertSee('Du hast bereits eine andere Aktion ausgeführt')
            // check that player can not do a Weiterbildung
            ->call('showWeiterbildung')
            ->assertSee('Du kannst nur eine Zeitsteinaktion pro Runde ausführen')
            // check that player can not do another Minijob
            ->call('doMinijob')
            ->assertSee('Du kannst nur eine Zeitsteinaktion pro Runde ausführen')
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is their turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Du bist am Zug');
    });

    it('takes out an insurance', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->call('showExpensesTab', 'insurances')
            ->assertSee([
                'Kredite',
                'Versicherungen',
                'Steuern und Abgaben',
                'Lebenshaltungskosten',
                'Haftpflichtversicherung',
                '100,00 €',
                'Private Unfallversicherung',
                '150,00 €',
                'Berufsunfähigkeitsversicherung',
                '500,00 €',
                'Summe Versicherungen',
                'Änderungen speichern'
            ]);
// ToDo: Versicherung abschließen
    });

    it('shows Lebensziel of current player', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->call('showPlayerLebensziel', $this->players[0])
            ->assertSee([
                'Dein Lebensziel',
                'Aufbau einer Selbstversorger Farm in Kanada',
                'Phase 1',
                'Um deinem nachhaltigen Traum näherzukommen, steht nun die internationale Ausbildung in ökologischer',
                'Landwirtschaft an, während du parallel die Formalitäten für eine dauerhafte Aufenthaltsgenehmigung in Kanada erledigst.',
                '50.000,00',
                'Phase 2',
                'Nach dem Abschluss planst du, am World Wide Opportunities on Organic Farms teilzunehmen und um die Welt zu reisen.',
                'Dabei möchtest du verschiedenste Selbstversorger-Projekte kennenlernen und Kontakte mit Gleichgesinnten knüpfen. Gestärkt',
                'durch dieses Netzwerk wirst du dich schließlich entscheiden, in Kanada deine eigene Farm zu gründen.',
//                '200.000,00',
                'Phase 3',
                'Deine Farm läuft erfolgreich und du nimmst internationale Freiwillige auf, die deinen ressourcenschonenden Lebensstil',
                'kennenlernen möchten. Durch Führungen und Vorträge verbreitet sich dein Konzept. Wird es dir gelingen, mehr Menschen für ein',
                'nachhaltiges, konsumreduziertes Leben zu begeistern?',
//                '500.000,00',
                'Phasenwechsel',
                'Kontostand',
                '30.000,00'
            ])
            ->assertSeeHtml([
                'Deine Kompetenzsteine im Bereich Freizeit &amp; Soziales: 0 von 2',
                'Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 0 von 1'
            ]);
    });

    it('displays error message when trying to finish turn without using a Zeitstein', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            // finish turn without playing a card
            ->call('spielzugAbschliessen')
            // check that player has all of their Zeitsteine
            ->assertSeeHtml('Player 0 hat noch 6 von 6 Zeitsteinen übrig.')
            // check that error message is displayed
            ->assertSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');
    });

    it('tries to finish turn without using a Zeitstein, closes error message, plays a card and finishes turn', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            // finish turn without playing a card
            ->call('spielzugAbschliessen')
            ->assertSee('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            // close error message
            ->call('closeNotification')
            // draw a card
            ->call('showCardActions', 'buk0', 'Bildung & Karriere')
            ->assertSee(['Sprachkurs', 'Mache einen Sprachkurs über drei Monate im Ausland.', '11.000,00', 'Karte spielen'])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte 'Sprachkurs'")
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that message is now logged
            ->assertSee(['Player 0', "spielt Karte 'Sprachkurs'"])
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');
    });

});


// Bildung und Karriere: 1 von 3 Zeitsteinen wurden platziert. Player 0: 1, Player 1: 0
// Freizeit & Soziales: 0 von 4 Zeitsteinen wurden platziert. Player 0: 0, Player 1: 0
// Beruf: 0 von 3 Zeitsteinen wurden platziert. Player 0: 0, Player 1: 0
// Finanzen: 0 von 4 Zeitsteinen wurden platziert. Player 0: 0, Player 1: 0

// Ehrenamtliches Engagement 1.200,00 €

// Minijob
// Weiterbildung
// Kredit aufnehmen
// Versicherung abschließen
// Lebensziel ansehen
