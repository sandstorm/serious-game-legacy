<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire\Views\Helpers;

use App\Livewire\GameUi;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

readonly class GameUiTester {

    public Testable $testableGameUi;

    public function __construct(GameId $gameId, private PlayerId $playerId, private string $playerName) {
        $this->testableGameUi = Livewire::test(GameUi::class, [
            'gameId' => $gameId,
            'myself' => $this->playerId
        ]);
    }

    public function startGame(
        $konjunkturphaseDefinition,
        $firstCardOfBildungAndKarriere,
        $firstCardOfSozialesAndFreizeit
    ): static {
        $this->testableGameUi
            ->assertSee([
                'Eine neue Konjunkturphase beginnt.',
                'Das nächste Szenario ist:',
                $konjunkturphaseDefinition->type->value
            ])
            ->call('nextKonjunkturphaseStartScreenPage')
            ->assertSee([$konjunkturphaseDefinition->type->value, $konjunkturphaseDefinition->description])
            ->call('startKonjunkturphaseForPlayer')
            ->assertSee([
                'Bildung & Karriere',
                'Freizeit & Soziales',
                'Beruf',
                'Finanzen',
                'Konjunktur',
                $konjunkturphaseDefinition->type->value,
                'Dein Lebensziel',
                'Ereignisprotokoll:',
                'Eine neue Konjunkturphase \'' . $konjunkturphaseDefinition->name . '\' beginnt.',
                $firstCardOfBildungAndKarriere->getTitle(),
                $firstCardOfSozialesAndFreizeit->getTitle(),
                'Jobbörse',
                'Investitionen',
                'Weiterbildung',
                'Minijob',
                'Kredit aufnehmen',
                'Versicherung abschließen',
                'Spielzug beenden'
            ]);

        return $this;
    }

}

