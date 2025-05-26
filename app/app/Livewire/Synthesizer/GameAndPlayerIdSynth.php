<?php

declare(strict_types=1);

namespace App\Livewire\Synthesizer;

use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

/**
 * PlayerId and GameId Value Objects need to be stored in Livewire components;
 * -> see https://livewire.laravel.com/docs/synthesizers for details.
 *
 * - They need to expose their property with a public property "value"
 * - They need to have static {@see self::fromString()} factory method.
 */
class GameAndPlayerIdSynth extends Synth
{
    public static string $key = 'vo';

    public static function match(mixed $target): bool
    {
        return $target instanceof GameId || $target instanceof PlayerId;
    }

    /**
     * @param mixed $target
     * @return array<mixed>
     */
    public function dehydrate(GameId|PlayerId $target): array
    {
        // @phpstan-ignore property.notFound
        return [$target->value, [
            'type' => get_class($target),
        ]];
    }

    /**
     * @param mixed $value
     * @param array<mixed> $metadata
     * @return GameId|PlayerId
     */
    public function hydrate(mixed $value, array $metadata): GameId|PlayerId
    {
        $type = $metadata['type'];
        return $type::fromString($value);
    }
}
