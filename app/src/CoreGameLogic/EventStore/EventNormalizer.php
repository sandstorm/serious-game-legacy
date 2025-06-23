<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\EventStore;

use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Command\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Command\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SteuernUndAbgabenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerWasMarkedAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Neos\EventStore\Model\Event;
use Neos\EventStore\Model\Event\EventData;
use Neos\EventStore\Model\Event\EventId;
use Neos\EventStore\Model\Event\EventType;

/**
 * Central authority to convert Game domain events to Event Store EventData and EventType, vice versa.
 *
 * - For normalizing (from classes to event store)
 * - For denormalizing (from event store to classes)
 *
 * @internal inside projections the event will already be denormalized
 */
final readonly class EventNormalizer
{
    private function __construct(
        /**
         * @var array<class-string<GameEventInterface>,EventType>
         */
        private array $fullClassNameToShortEventType,
        /**
         * @var array<string,class-string<GameEventInterface>>
         */
        private array $shortEventTypeToFullClassName,
    ) {
    }

    /**
     * @internal never instantiate this object yourself
     */
    public static function create(): self
    {
        /** @var array<class-string<GameEventInterface>> $supportedEventClassNames */
        $supportedEventClassNames = [
            CardsWereShuffled::class,
            CardWasActivated::class,
            CardWasSkipped::class,
            EreignisWasTriggered::class,
            GameWasStarted::class,
            JobOffersWereRequested::class,
            JobOfferWasAccepted::class,
            KonjunkturphaseHasEnded::class,
            KonjunkturphaseWasChanged::class,
            LebenshaltungskostenForPlayerWereCorrected::class,
            LebenshaltungskostenForPlayerWereEntered::class,
            LebenszielWasSelected::class,
            MinijobWasDone::class,
            NameForPlayerWasSet::class,
            PlayerColorWasSelected::class,
            PlayerHasCompletedMoneysheetForCurrentKonjunkturphase::class,
            PlayerHasStartedKonjunkturphase::class,
            PlayerWasMarkedAsReadyForKonjunkturphaseChange::class,
            PreGameStarted::class,
            SpielzugWasEnded::class,
            SteuernUndAbgabenForPlayerWereCorrected::class,
            SteuernUndAbgabenForPlayerWereEntered::class,
            InsuranceForPlayerWasConcluded::class,
            InsuranceForPlayerWasCancelled::class,
            LoanWasTakenOutForPlayer::class,
        ];

        $fullClassNameToShortEventType = [];
        $shortEventTypeToFullClassName = [];

        foreach ($supportedEventClassNames as $fullEventClassName) {
            $shortEventClassPosition = strrpos($fullEventClassName, '\\') !== false ? strrpos($fullEventClassName, '\\') : 0;
            $shortEventClassName = substr($fullEventClassName, $shortEventClassPosition + 1);

            $fullClassNameToShortEventType[$fullEventClassName] = EventType::fromString($shortEventClassName);
            $shortEventTypeToFullClassName[$shortEventClassName] = $fullEventClassName;
        }

        return new self(
            fullClassNameToShortEventType: $fullClassNameToShortEventType,
            shortEventTypeToFullClassName: $shortEventTypeToFullClassName
        );
    }

    /**
     * @return class-string<GameEventInterface>
     */
    public function getEventClassName(Event $event): string
    {
        return $this->shortEventTypeToFullClassName[$event->type->value] ?? throw new \InvalidArgumentException(
            sprintf('Failed to denormalize event "%s" of type "%s"', $event->id->value, $event->type->value),
            1651839705
        );
    }

    public function normalize(DecoratedEvent|GameEventInterface $event): Event
    {
        $eventId = $event instanceof DecoratedEvent && $event->eventId !== null ? $event->eventId : EventId::create();
        $eventMetadata = $event instanceof DecoratedEvent ? $event->eventMetadata : null;
        $causationId = $event instanceof DecoratedEvent ? $event->causationId : null;
        $correlationId = $event instanceof DecoratedEvent ? $event->correlationId : null;
        $event = $event instanceof DecoratedEvent ? $event->innerEvent : $event;
        return new Event(
            $eventId,
            $this->getEventType($event),
            $this->getEventData($event),
            $eventMetadata,
            $causationId,
            $correlationId,
        );
    }

    public function denormalize(Event $event): GameEventInterface
    {
        $eventClassName = $this->getEventClassName($event);
        try {
            $eventDataAsArray = json_decode($event->data->value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException(
                sprintf('Failed to decode data of event with type "%s" and id "%s": %s', $event->type->value, $event->id->value, $exception->getMessage()),
                1651839461
            );
        }
        if (!is_array($eventDataAsArray)) {
            throw new \RuntimeException(sprintf('Expected array got %s', $eventDataAsArray));
        }
        /** {@see GameEventInterface::fromArray()} */
        return $eventClassName::fromArray($eventDataAsArray);
    }

    private function getEventData(GameEventInterface $event): EventData
    {
        try {
            $eventDataAsJson = json_encode($event, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Failed to normalize event of type "%s": %s',
                    get_debug_type($event),
                    $exception->getMessage()
                ),
                1651838981
            );
        }
        return EventData::fromString($eventDataAsJson);
    }

    private function getEventType(GameEventInterface $event): EventType
    {
        $className = get_class($event);

        return $this->fullClassNameToShortEventType[$className] ?? throw new \RuntimeException(
            'Event type ' . get_class($event) . ' not registered'
        );
    }
}
