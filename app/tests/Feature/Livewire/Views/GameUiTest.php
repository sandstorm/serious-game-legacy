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
                'Minijob'
            ])
            // draw a card
            ->call('showCardActions', 'buk0', 'Bildung & Karriere')
            ->assertSee(['Sprachkurs', 'Mache einen Sprachkurs über drei Monate im Ausland.', 'Karte spielen'])
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte 'Sprachkurs'")
            // check that player has all of his Zeitsteine
            ->assertSeeHtml('Player 0 hat noch 6 von 6 Zeitsteinen übrig.')
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that message is now logged
            ->assertSee(['Player 0', "spielt Karte 'Sprachkurs'"])
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            // check that player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 5 von 6 Zeitsteinen übrig.');
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
            ->assertSee(['Sprachkurs', 'Mache einen Sprachkurs über drei Monate im Ausland.', 'Karte spielen'])
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

