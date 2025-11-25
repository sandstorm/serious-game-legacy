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
        $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);

        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Konjunktur')
            // draw a card
            ->call("showCardActions", 'buk0', 'Bildung & Karriere')
            ->assertSee('Karte spielen')
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte 'Sprachkurs'")
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that message is now logged
            ->assertSee("spielt Karte 'Sprachkurs'")
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');
    });

    it('displays error message when trying to finish turn without using a Zeitstein', function () {
        $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);

        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Konjunktur')
            // finish turn without playing a card
            ->call('spielzugAbschliessen')
            // check that error message is displayed
            ->assertSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');
    });

    it('tries to finish turn without using a Zeitstein, closes error message, plays a card and finishes turn', function () {
        $this->app->instance(ForCoreGameLogic::class, $this->coreGameLogic);

        /** @var TestCase $this */
        Livewire::test(GameUi::class, [
            'gameId' => $this->gameId,
            'myself' => $this->players[0],
        ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee('Konjunktur')
            // finish turn without playing a card
            ->call('spielzugAbschliessen')
            ->assertSee('Du musst erst einen Zeitstein für eine Aktion ausgeben')
            // close error message
            ->call('closeNotification')
            // draw a card
            ->call("showCardActions", 'buk0', 'Bildung & Karriere')
            ->assertSee('Karte spielen')
            // check that message is not in Ereignisprotokoll
            ->assertDontSee("spielt Karte 'Sprachkurs'")
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that message is now logged
            ->assertSee("spielt Karte 'Sprachkurs'")
            // finish turn
            ->call('spielzugAbschliessen')
            ->assertDontSee('Du musst erst einen Zeitstein für eine Aktion ausgeben');
    });

});

