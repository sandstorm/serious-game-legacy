<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MaybeTriggerEreignis;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;
use Domain\CoreGameLogic\Feature\Spielzug\Event\BerufsunfaehigkeitsversicherungWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerGotAChild;
use Domain\CoreGameLogic\Feature\Spielzug\State\EreignisPrerequisiteChecker;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
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

    private function getRandomEreignisForCategory(
        GameEvents $gameEvents,
        MaybeTriggerEreignis $command
    ): EreignisCardDefinition {
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
            throw new \RuntimeException("No EreignisCard matches the current requirements",
                1753874959); // We should always have cards
        }
        $randomizer = new Randomizer();
        return $randomizer->shuffleArray($filteredCards)[0];
    }

    private function getGuthabenChangesBasedOnPlayersInsurances(
        GameEvents $gameEvents,
        MaybeTriggerEreignis $command,
        EreignisCardDefinition $ereignisCardDefinition
    ): ResourceChanges {
        $resourceChanges = $ereignisCardDefinition->getResourceChanges();

        $modifierIdsAsString = array_map(fn($id) => $id->value, $ereignisCardDefinition->getModifierIds());
        // Check if Ereignis is insurable and player has the specific insurance -> if so: modify guthabenChange
        if (
            in_array(ModifierId::PRIVATE_UNFALLVERSICHERUNG->value, $modifierIdsAsString, true)
                // InsuranceId::create(2) creates an Unfallversicherung-Id
                && !MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $command->playerId, InsuranceId::create(2))
            || in_array(ModifierId::HAFTPFLICHTVERSICHERUNG->value, $modifierIdsAsString, true)
                // InsuranceId::create(2) creates a Haftpflichtversicherung-Id
                && MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $command->playerId, InsuranceId::create(1))) {
            $resourceChanges = new ResourceChanges(
                guthabenChange: new MoneyAmount(0),
                zeitsteineChange: $resourceChanges->zeitsteineChange,
                bildungKompetenzsteinChange: $resourceChanges->bildungKompetenzsteinChange,
                freizeitKompetenzsteinChange: $resourceChanges->freizeitKompetenzsteinChange,
            );
        }

        return $resourceChanges;
    }

    private function triggerAdditionalEvents(
        GameEvents $gameEvents,
        MaybeTriggerEreignis $command,
        EreignisCardDefinition $ereignisCardDefinition
    ): GameEventsToPersist {
        $additionalEvents = GameEventsToPersist::empty();
        $modifierIdsAsString = array_map(fn($id) => $id->value, $ereignisCardDefinition->getModifierIds());
        if (in_array(ModifierId::JOBVERLUST->value, $modifierIdsAsString, true) ||
            in_array(ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG->value, $modifierIdsAsString, true)) {
            $additionalEvents = (new SpielzugCommandHandler())->handle(QuitJob::create($command->playerId),
                $gameEvents);
        }

        if (in_array(ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG->value, $modifierIdsAsString, true)
            && MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $command->playerId, InsuranceId::create(3))) {
            $additionalEvents = $additionalEvents
                ->withAppendedEvents(new BerufsunfaehigkeitsversicherungWasActivated(
                    playerId: $command->playerId,
                    year: KonjunkturphaseState::getCurrentYear($gameEvents),
                    gehalt: PlayerState::getCurrentGehaltForPlayer($gameEvents, $command->playerId),
                ));
        }

        // TODO don't use title -> use ModifierId
        if($ereignisCardDefinition->getTitle() === "Geburt") {
            return $additionalEvents->withAppendedEvents(new PlayerGotAChild(
                playerId: $command->playerId,
            ));
        }

        return $additionalEvents;
    }

    private function handleTriggerEreignis(MaybeTriggerEreignis $command, GameEvents $gameEvents): GameEventsToPersist
    {
        if (!$this->doesTrigger()) {
            return GameEventsToPersist::empty();
        }
        $ereignisDefinition = $this->getRandomEreignisForCategory($gameEvents, $command);
        $resourceChanges = $this->getGuthabenChangesBasedOnPlayersInsurances($gameEvents, $command,
            $ereignisDefinition);

        return GameEventsToPersist::with(
            new EreignisWasTriggered(
                playerId: $command->playerId,
                ereignisCardId: $ereignisDefinition->getId(),
                playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $command->playerId),
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
                resourceChanges: $resourceChanges,
            ),
            ...$this->triggerAdditionalEvents($gameEvents, $command, $ereignisDefinition)->events,
        );
    }
}
