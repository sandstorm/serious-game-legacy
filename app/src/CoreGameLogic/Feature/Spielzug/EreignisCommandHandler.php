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
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Random\Randomizer;

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

    private function getRandomEreignisForCategory(GameEvents $gameEvents, MaybeTriggerEreignis $command): EreignisCardDefinition
    {
        $ereignisCardCategory = match ($command->categoryId) {
            CategoryId::BILDUNG_UND_KARRIERE => CategoryId::EREIGNIS_BILDUNG_UND_KARRIERE,
            CategoryId::SOZIALES_UND_FREIZEIT => CategoryId::EREIGNIS_SOZIALES_UND_FREIZEIT,
            default => throw new \RuntimeException("Cannot map category " . $command->categoryId->value . " to ereignis category"),
        };
        /** @var EreignisCardDefinition[] $allCards */
        $allCards = CardFinder::getInstance()->getCardDefinitionsByCategoryAndPhase(
            $ereignisCardCategory,
            PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $command->playerId),
        );
        $filteredCards = array_filter($allCards, function ($cardDefinition) use ($gameEvents, $command) {
            $hasPlayerAllPrerequisites = true;
            foreach ($cardDefinition->getEreignisRequirementIds() as $requirementId) {
                $hasPlayerAllPrerequisites = $hasPlayerAllPrerequisites &&
                    EreignisPrerequisiteChecker::forStream($gameEvents)
                        ->hasPlayerPrerequisites($command->playerId, $requirementId);
            }
            return $hasPlayerAllPrerequisites;
        });
        if (count($filteredCards) === 0) {
            throw new \RuntimeException("No EreignisCard matches the current requirements", 1753874959); // We should always have cards
        }
        $randomizer = new Randomizer();
        return $randomizer->shuffleArray($filteredCards)[0];
    }

    private function handleTriggerEreignis(MaybeTriggerEreignis $command, GameEvents $gameEvents): GameEventsToPersist
    {
        if (!$this->doesTrigger()) {
            return GameEventsToPersist::empty();
        }
        $ereignisDefinition = $this->getRandomEreignisForCategory($gameEvents, $command);
        return GameEventsToPersist::with(
            new EreignisWasTriggered(
                playerId: $command->playerId,
                ereignisCardId: $ereignisDefinition->getId(),
                playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $command->playerId),
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
            )
        );
    }
}
