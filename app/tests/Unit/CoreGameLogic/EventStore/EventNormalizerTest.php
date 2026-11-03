<?php

declare(strict_types=1);

namespace Tests\CoreGameLogic\EventStore;

use Domain\CoreGameLogic\EventStore\EventNormalizer;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasStarted;
use Neos\EventStore\Model\Event;
use Neos\EventStore\Model\Event\EventData;
use Neos\EventStore\Model\Event\EventId;
use Neos\EventStore\Model\Event\EventType;

/**
 * @return array<class-string<GameEventInterface>>
 */
function findAllGameEventClassNames(): array
{
    $srcDirectory = realpath(__DIR__ . '/../../../../src');
    $gameEventClassNames = [];
    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($srcDirectory . '/CoreGameLogic'));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        // PSR-4: Domain\ => src/
        $className = 'Domain\\' . str_replace('/', '\\', substr($file->getPathname(), strlen($srcDirectory) + 1, -4));
        if (class_exists($className) && is_subclass_of($className, GameEventInterface::class)
            && new \ReflectionClass($className)->isInstantiable()) {
            $gameEventClassNames[] = $className;
        }
    }
    return $gameEventClassNames;
}

describe('EventNormalizer', function () {
    /**
     * WHY: A game event that is not registered in the EventNormalizer cannot be stored. Tests only notice this
     * if they trigger the event, so this test protects events of features that are not (yet) tested.
     */
    it('can denormalize every GameEvent', function () {
        $gameEventClassNames = findAllGameEventClassNames();
        // make sure the class discovery works at all
        expect($gameEventClassNames)->toContain(SpielzugWasStarted::class);

        $eventNormalizer = EventNormalizer::create();
        foreach ($gameEventClassNames as $gameEventClassName) {
            $shortClassName = substr($gameEventClassName, strrpos($gameEventClassName, '\\') + 1);
            $storedEvent = new Event(EventId::create(), EventType::fromString($shortClassName), EventData::fromString('{}'));
            expect($eventNormalizer->getEventClassName($storedEvent))->toBe($gameEventClassName);
        }
    });
});
