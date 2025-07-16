<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MaybeTriggerEreignis;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\State\EreignisPrerequisiteChecker;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class EreignisCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof MaybeTriggerEreignis;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            MaybeTriggerEreignis::class => $this->handleTriggerEreignis($command, $gameEvents),
        };
    }

    private function doesTrigger(): bool
    {
        // @phpstan-ignore disallowed.function
        return rand(1, 4) === 1;
    }

    private function getRandomEreignis(GameEvents $gameEvents, PlayerId $playerId): EreignisCardDefinition
    {
        /** @var EreignisCardDefinition[] $allCards */
        $allCards = CardFinder::getInstance()->getCardsForPile(PileId::EREIGNISSE_BILDUNG_UND_KARRIERE_PHASE_1);
        $filteredCards = array_filter($allCards, function ($cardDefinition) use ($gameEvents, $playerId) {
            $hasPlayerAllPrerequisites = true;
            foreach ($cardDefinition->ereignisRequirementIds as $requirementId) {
                $hasPlayerAllPrerequisites = $hasPlayerAllPrerequisites && EreignisPrerequisiteChecker::forStream($gameEvents)->hasPlayerPrerequisites($playerId, $requirementId);
            }
            return $hasPlayerAllPrerequisites;
        });
        return $filteredCards[array_rand($filteredCards)];
    }

    private function handleTriggerEreignis(MaybeTriggerEreignis $command, GameEvents $gameEvents): GameEventsToPersist
    {
        if (!$this->doesTrigger()) {
            return GameEventsToPersist::empty();
        }
        $ereignisDefinition = $this->getRandomEreignis($gameEvents, $command->playerId);
        return GameEventsToPersist::with(
            new EreignisWasTriggered(
                playerId: $command->playerId,
                ereignisCardId: $ereignisDefinition->id,
                playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $command->playerId),
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
            )
        );
    }
}
