<?php
namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;

class GameUiTester
{
    public readonly \Livewire\Features\SupportTesting\Testable $testableGameUi;

    public function __construct(GameId $gameId, private readonly PlayerId $playerId, private readonly string $playerName) {
        $this->testableGameUi = Livewire::test(GameUi::class, [
            'gameId' => $gameId,
            'myself' => $playerId
        ]);

    }

    public function drawAndPlayCard(string $cardId, string $categoryName) {
        $davorZeitsteine = $this->uebrigeZeitsteine();

        $this->testableGameUi
            ->call('showCardActions', 'buk0', 'Bildung & Karriere')
            // play card
            ->call('activateCard', 'Bildung & Karriere')
            // check that first player has used 1 Zeitstein
            ->assertSeeHtml('Player 0 hat noch 5 von 6 Zeitsteinen übrig.')
            // check that first player got 1 Kompetenzstein for Bildung & Karriere
            ->assertSeeHtml('Deine Kompetenzsteine im Bereich Bildung &amp; Karriere: 1 von 1');

        $danachZeitsteine = $this->uebrigeZeitsteine();
        Assert::assertEquals($davorZeitsteine - 1, $danachZeitsteine, 'Zeitsteine um 1 reduziert');
    }

    private function uebrigeZeitsteine(): int {
        $playerResources = PlayerState::getResourcesForPlayer($this->testableGameUi->getGameEvents(), $this->playerId);
        $zeitsteine = $playerResources->zeitsteineChange;
        $this->testableGameUi->assertSeeHtml($this->playerName . ' hat noch ' . $zeitsteine . ' von 6 Zeitsteinen übrig.');

        return $zeitsteine;
    }
}
