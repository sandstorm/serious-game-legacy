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
    public static $key = 'vo';

    public static function match($target)
    {
        return $target instanceof ValueObjectInterface;
    }

    public function dehydrate($target)
    {
        return [$target->value, [
            'type' => get_class($target),
        ]];
    }

    public function hydrate($value, $metadata)
    {
        $type = $metadata['type'];
        return new $type($value);
    }
}
