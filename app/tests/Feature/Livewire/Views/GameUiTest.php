<?php

namespace Tests\Feature\Livewire;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0]
        ])
            ->assertStatus(200);
    });

    it('plays a card from Bildung & Karriere and finishes turn', function () {

        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->assertSee(['Eine neue Konjunkturphase beginnt.', 'Das nächste Szenario ist:', $this->konjunkturphaseDefinition->type->value])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->assertSee([$this->konjunkturphaseDefinition->type->value, $this->konjunkturphaseDefinition->description])
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee([
                'Konjunktur',
                $this->konjunkturphaseDefinition->type->value,
                'Bildung & Karriere',
                'Freizeit & Soziales',
                'Beruf',
                'Finanzen',
                'Dein Lebensziel',
                'Ereignisprotokoll:',
                "Eine neue Konjunkturphase 'Erste Erholung' beginnt.",
                'Sprachkurs',
                'Ehrenamtliches Engagement',
                'Jobbörse',
                'Investitionen',
                'Weiterbildung',
                'Minijob',
                'Kredit aufnehmen',
                'Versicherung abschließen',
                'Spielzug beenden'
            ])
            // draw a card
            ->call('showCardActions', 'buk0', 'Bildung & Karriere')
            ->assertSee(['Sprachkurs', 'Mache einen Sprachkurs über drei Monate im Ausland.', '11.000,00', 'Karte spielen'])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte 'Sprachkurs'")
            // check that player has all of his Zeitsteine
            ->assertSeeHtml('Player 0 hat noch 6 von 6 Zeitsteinen übrig.')
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that message is now logged
            ->assertSee(['Player 0', "spielt Karte 'Sprachkurs'"])
            // check that player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 5 von 6 Zeitsteinen übrig.')
            // check that 1 Zeitstein is used for category Bildung & Karriere
            ->assertSeeHtml('1 von 3 Zeitsteinen wurden platziert. Player 0: 1, Player 1: 0')
            // check that player got 1 Kompetenzstein for Bildung & Karriere
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 1 von 1')
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that opponent player receives a message that it is his turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Du bist am Zug');
    });

    it('plays a card from Freizeit & Soziales and finishes turn', function () {
        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            // draw a card
            ->call('showCardActions', 'suf0', 'Freizeit & Soziales')
            ->assertSee([
                'Ehrenamtliches Engagement',
                'Du engagierst dich ehrenamtlich für eine Organisation, die es Menschen mit Behinderung ermöglicht einen genialen Urlaub mit Sonne, Strand und Meer zu erleben. Du musst die Kosten dafür allerdings selbst tragen.',
                '1.200,00',
                'Karte spielen'
            ])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte 'Ehrenamtliches Engagement'")
            // check that player has all of his Zeitsteine
            ->assertSeeHtml('Player 0 hat noch 6 von 6 Zeitsteinen übrig.')
            // play card
            ->call('activateCard', 'Freizeit & Soziales')
            // check that message is now logged
            ->assertSee(['Player 0', "spielt Karte 'Ehrenamtliches Engagement'"])
            // check that player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 5 von 6 Zeitsteinen übrig.')
            // check that 1 Zeitstein is used for category Freizeit & Soziales
            ->assertSeeHtml('1 von 4 Zeitsteinen wurden platziert. Player 0: 1, Player 1: 0')
            // check that player got 1 Kompetenzstein for Freizeit & Soziales
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Freizeit &amp; Soziales: 1 von 2')
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            // check that player can not play a card after finishing turn
            ->call('showCardActions', 'suf0', 'Freizeit & Soziales')
            ->call('activateCard', 'Bildung & Karriere')
            ->assertSee('Du bist gerade nicht dran');

        // check that opponent player receives a message that it is his turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Du bist am Zug');
    });

    it('get enough abilities and accept a job offer', function () {
        /** @var TestCase $this */

        // first player plays first card for Bildung & Karriere and finishes turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            // draw a card from Bildung & Karriere
            ->call('showCardActions', 'buk0', 'Bildung & Karriere')
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that first player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 5 von 6 Zeitsteinen übrig.')
            // check that first player got 1 Kompetenzstein for Bildung & Karriere
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 1 von 1')
            // finish turn
            ->call('spielzugAbschliessen');

        // second player uses 1 Zeitstein and finishes turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Du bist am Zug')
            // draw a card from Bildung & Karriere
            ->call('showCardActions', 'suf0', 'Freizeit & Soziales')
            // play card
            ->call('activateCard', 'Freizeit & Soziales')
            // check that second player has used 1 Zeitstein
            ->assertSeeHtml('Player 1 hat noch 5 von 6 Zeitsteinen übrig.')
            // check that second player got 1 Kompetenzstein for Freizeit & Soziales
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Freizeit &amp; Soziales: 1 von 2')
            // finish turn
            ->call('spielzugAbschliessen');

        // first player plays second card for Bildung & Karriere and finishes turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->assertSee('Du bist am Zug')
            // draw a card from Bildung & Karriere
            ->call('showCardActions', 'buk1', 'Bildung & Karriere')
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that first player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 4 von 6 Zeitsteinen übrig.')
            // check that first player got 2 Kompetenzstein for Bildung & Karriere
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 2 von 1')
            // finish turn
            ->call('spielzugAbschliessen');;

        // second player uses 1 Zeitstein and finishes turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->assertSee('Du bist am Zug')
            // draw a card from Bildung & Karriere
            ->call('showCardActions', 'suf0', 'Freizeit & Soziales')
            // play card
            ->call('activateCard', 'Freizeit & Soziales')
            // check that second player has used 1 Zeitstein
            ->assertSeeHtml('Player 1 hat noch 4 von 6 Zeitsteinen übrig.')
            // check that second player got 1 Kompetenzstein for Freizeit & Soziales
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Freizeit &amp; Soziales: 0 von 2')
            // finish turn
            ->call('spielzugAbschliessen');

        // first player accepts a job and finishes turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->assertSee('Du bist am Zug')
            ->call('showJobOffers')
            ->assertSee([
                'Ein Job kostet Zeit. Pro Jahr bleibt dir ein Zeitstein weniger.',
                'Deine bisher erworbenen Kompetenzen:',
                'Fachinformatikerin',
                '34.000,00',
                'Das mache ich!',
                'Voraussetzungen:'
            ])
            ->assertSeeHtml([
                'Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 2 von 1',
                'Deine Kompetenzsteine im Bereich Freizeit &amp; Soziales: 2 von 2'
            ])
            ->call('applyForJob', 'j100')
            // check that first player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 2 von 6 Zeitsteinen übrig.')
            // check that 1 Zeitstein is used for category Beruf
            ->assertSeeHtml('1 von 3 Zeitsteinen wurden platziert. Player 0: 1, Player 1: 0')
            // check that first player activates Kompetenzstein Beruf
            ->assertSeeHtml('Du hast einen Job (Ein Zeitstein ist dauerhaft gebunden)')
            // check that message is now logged
            ->assertSee([
                "nimmt Job 'Fachinformatikerin' an",
                'Mein Job: ',
                '34.000,00 €'
            ])
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');

        // check that second player receives a message that it is his turn
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[1],
        ])
            ->assertSee('Du bist am Zug');
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
            // check that player has all of his Zeitsteine
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

