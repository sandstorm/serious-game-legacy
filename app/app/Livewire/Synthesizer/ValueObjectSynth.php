<?php

namespace App\Livewire\Synthesizer;

use Domain\CoreGameLogic\Dto\ValueObjectInterface;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

/**
 * Support Value Objects in Livewire
 * {@see ValueObjectInterface} for explanation of contract
 */
class ValueObjectSynth extends Synth
{
    public static string $key = 'vo';

    public static function match(mixed $target): bool
    {
        return $target instanceof ValueObjectInterface;
    }

    /**
     * @param ValueObjectInterface $target
     * @return array<mixed>
     */
    public function dehydrate(ValueObjectInterface $target): array
    {
        // @phpstan-ignore property.notFound
        return [$target->value, [
            'type' => get_class($target),
        ]];
    }

    /**
     * @param mixed $value
     * @param array<mixed> $metadata
     * @return ValueObjectInterface
     */
    public function hydrate(mixed $value, array $metadata): ValueObjectInterface
    {
        $type = $metadata['type'];
        // @phpstan-ignore return.type
        return new $type($value);
    }
}
