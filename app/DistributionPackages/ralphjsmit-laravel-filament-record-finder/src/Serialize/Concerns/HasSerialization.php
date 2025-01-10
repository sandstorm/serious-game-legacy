<?php

namespace RalphJSmit\Filament\RecordFinder\Serialize\Concerns;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use Closure;
use Filament\Support;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use RalphJSmit\Filament\RecordFinder\Serialize\Data\SerializableClosureObject;
use RalphJSmit\Filament\RecordFinder\Serialize\Data\SerializedEloquentQuery;
use ReflectionClass;
use RuntimeException;

trait HasSerialization
{
    protected function serializeOriginal(array|null|int|float|string|bool|Closure|Support\Components\Component|Support\Components\ViewComponent|Builder $original): string
    {
        $serialized = match (true) {
            is_array($original) => $this->serializeOriginalFromArray($original),
            is_null($original) => $this->serializeOriginalFromNull($original),
            is_numeric($original) || is_string($original) || is_bool($original) => $this->serializeOriginalFromNumericStringBoolean($original),
            $original instanceof Closure => $this->serializeOriginalFromClosure($original),
            $original instanceof Support\Components\Component => $this->serializeOriginalFromSupportComponent($original),
            $original instanceof Support\Components\ViewComponent => $this->serializeOriginalFromSupportViewComponent($original),
            $original instanceof Builder => $this->serializeOriginalFromEloquentBuilder($original),
            default => throw new RuntimeException('Unable to serialize original.'),
        };

        return encrypt($serialized, serialize: false);
    }

    protected function serializeOriginalFromArray(array $original): string
    {
        $source = [];

        foreach ($original as $originalKey => $originalValue) {
            $source[$originalKey] = $this->serializeOriginal($originalValue);
        }

        return serialize($source);
    }

    protected function serializeOriginalFromNull(null $original): string
    {
        return serialize($original);
    }

    protected function serializeOriginalFromNumericStringBoolean(int|float|string|bool $original): string
    {
        return serialize($original);
    }

    protected function serializeOriginalFromClosure(Closure $original): string
    {
        return serialize(new SerializableClosure($original));
    }

    protected function serializeOriginalFromSupportComponent(Support\Components\Component $original): string
    {
        return serialize($this->getSerializableClosureObject($original));
    }

    protected function serializeOriginalFromSupportViewComponent(Support\Components\ViewComponent $original): string
    {
        return serialize($this->getSerializableClosureObject($original));
    }

    protected function serializeOriginalFromEloquentBuilder(Builder $query): string
    {
        return serialize(new SerializedEloquentQuery(EloquentSerializeFacade::serialize(clone $query)));
    }

    /**
     * @internal
     */
    protected function getSerializableClosureObject(object $original): SerializableClosureObject
    {
        // Clone the original first, because the code below resets all closure properties to
        // their default values using reflection. If the object is referenced elsewhere,
        // this will break the code depending on these properties being set correctly.
        $reflectionClass = new ReflectionClass($originalClone = clone $original);

        $serializableClosureProperties = $serializableClosureObjectProperties = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->isInitialized($originalClone)) {
                $value = $reflectionProperty->getValue($originalClone);

                if ($value instanceof Closure) {
                    $reflectionClosure = new ReflectionClosure($value);

                    if ($reflectionClosure->isBindingRequired()) {
                        if ($reflectionClosure->getClosureThis() === $original) {
                            // If the binding is a simple binding to the current `$this` (like Filament Filters do in the
                            // `setUp()` method when passing a closure to the `->indicateUsing()`. We will bind `$this`
                            // now to the `$originalClone` without any closure properties, so that the serializable
                            // closure will not try to serialize any closure properties on the `$original` as
                            // being the `$this` bound to the closure. Any other `$this` reference we will
                            // keep, in the hopes of that not crashing the program and not bothering.
                            $value = $value->bindTo($originalClone);
                        }
                    } else {
                        // No need for the SerializableClosure to spend time serializing a `$this`
                        // that is not even used in the first place in the closure itself.
                        $value = $value->bindTo(null);
                    }

                    $serializableClosureProperties[$reflectionProperty->getName()] = new SerializableClosure($value);

                    $reflectionProperty->setValue($originalClone, $reflectionProperty->getDefaultValue());
                }

                // If a property is another Filament type of object, then that object could contain closure properties as well.
                // For example, when serializing a table action, that contains infolist or form components with closures.
                if ($value instanceof Support\Components\Component || $value instanceof Support\Components\ViewComponent) {
                    $serializableClosureObjectProperties[$reflectionProperty->getName()] = $this->getSerializableClosureObject($value);

                    $reflectionProperty->setValue($originalClone, $reflectionProperty->getDefaultValue());
                }

                if (is_array($value)) {
                    $value = Arr::map($value, function (mixed $arrayValue) {
                        if ($arrayValue instanceof Closure) {
                            return $this->getSerializableClosureObject($arrayValue);
                        }

                        if ($arrayValue instanceof Support\Components\Component || $arrayValue instanceof Support\Components\ViewComponent) {
                            return $this->getSerializableClosureObject($arrayValue);
                        }

                        return $arrayValue;
                    });

                    $reflectionProperty->setValue($originalClone, $value);
                }
            }
        }

        return new SerializableClosureObject(
            originalWithoutClosureProperties: $originalClone,
            serializableClosureProperties: $serializableClosureProperties,
            serializableClosureObjectProperties: $serializableClosureObjectProperties,
        );
    }
}
