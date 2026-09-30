<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\GameResource;
use App\Models\Game;
use Domain\CoreGameLogic\CoreGameLogicApp;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\GameEventStore;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Neos\EventStore\Helper\InMemoryEventStore;
use Neos\EventStore\Model\EventStream\ExpectedVersion;

beforeEach(function () {
    $this->p1 = PlayerId::fromString('p1');
    $this->game = (new Game())->forceFill(['id' => 'game1']);

    // a card that was removed from the game by an import, but is referenced by an old game
    CardFinder::getInstance()->overrideLegacyCardsForTesting([
        'legacy1' => new EreignisCardDefinition(
            id: new CardId('legacy1'),
            categoryId: CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
            title: 'removed Ereignis',
            description: 'for testing',
        ),
    ]);

    // an old game (created on a different version of the game) that has triggered the removed card
    $gameEventStore = new GameEventStore(new InMemoryEventStore());
    $gameEventStore->commit(GameId::fromString('game1'), GameEventsToPersist::with(
        new PreGameStarted(
            playerIds: [$this->p1, PlayerId::fromString('p2')],
            resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(Configuration::STARTKAPITAL_VALUE)),
            definitionsVersion: Configuration::DEFINITIONS_VERSION - 1,
        ),
        new EreignisWasTriggered(
            playerId: $this->p1,
            ereignisCardId: new CardId('legacy1'),
            playerTurn: new PlayerTurn(1),
            year: new Year(1),
            resourceChanges: new ResourceChanges(guthabenChange: new MoneyAmount(-500)),
        ),
    ), ExpectedVersion::NO_STREAM());
    $this->coreGameLogic = new CoreGameLogicApp($gameEventStore);
});

afterEach(function () {
    CardFinder::getInstance()->overrideLegacyCardsForTesting([]);
});

describe('getLogs (json export)', function () {
    it('exports games created on a different version of the game that reference legacy cards', function () {
        $logs = json_decode(GameResource::getLogsForTesting($this->game, $this->coreGameLogic), true, flags: JSON_THROW_ON_ERROR);

        expect($logs)->toHaveCount(2)
            ->and($logs[0]['event'])->toBe('PreGameStarted')
            ->and($logs[0]['data']['definitionsVersion'])->toBe(Configuration::DEFINITIONS_VERSION - 1)
            ->and($logs[1]['event'])->toBe('EreignisWasTriggered')
            ->and($logs[1]['data']['ereignisCardId'])->toBe('legacy1')
            // the stored values are exported, not the ones of the (current) card definition
            ->and($logs[1]['data']['resourceChanges']['guthabenChange'])->toEqual(-500);
    });

    it('fails without the legacy card (this is what the legacy cards prevent)', function () {
        CardFinder::getInstance()->overrideLegacyCardsForTesting([]);

        GameResource::getLogsForTesting($this->game, $this->coreGameLogic);
    })->throws(RuntimeException::class, 'Card [CardId: legacy1] does not exist');
});
