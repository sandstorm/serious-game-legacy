<?php

namespace RalphJSmit\Filament\RecordFinder\Serialize\Concerns;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use Closure;
use Filament\Support;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Laravel\SerializableClosure\SerializableClosure;
use RalphJSmit\Filament\RecordFinder\Serialize\Data\SerializableClosureObject;
use RalphJSmit\Filament\RecordFinder\Serialize\Data\SerializedEloquentQuery;
use ReflectionClass;
use ReflectionFunction;

trait HasUnserialization
{
    protected function unserializeSource(string $source): array | null | int | float | string | Closure | Support\Components\Component | Support\Components\ViewComponent | Builder
    {
        $unserialized = unserialize(
            decrypt($source, unserialize: false)
        );

        if (is_array($unserialized)) {
            return $this->unserializeSourceFromArray($unserialized);
        }

        if (is_null($unserialized)) {
            return $this->unserializeSourceFromNull($unserialized);
        }

        if (is_numeric($unserialized) || is_string($unserialized) || is_bool($unserialized)) {
            return $this->unserializeSourceFromNumericStringBoolean($unserialized);
        }

        if ($unserialized instanceof SerializableClosure) {
            return $this->unserializeSourceFromSerializableClosure($unserialized);
        }

        if ($unserialized instanceof SerializableClosureObject) {
            return $this->unserializeSourceFromSerializableClosureObject($unserialized);
        }

        if ($unserialized instanceof SerializedEloquentQuery) {
            return $this->unserializeSourceFromSerializedEloquentQuery($unserialized);
        }

        throw new RuntimeException('Unable to unserialize source.');
    }

    protected function unserializeSourceFromArray(array $source): array
    {
        $original = [];

        foreach ($source as $sourceKey => $sourceValue) {
            $original[$sourceKey] = $this->unserializeSource($sourceValue);
        }

        return $original;
    }

    protected function unserializeSourceFromNull(null $source): null
    {
        return $source;
    }

    protected function unserializeSourceFromNumericStringBoolean(int | float | string | bool $source): int | float | string | null | bool
    {
        return $source;
    }

    protected function unserializeSourceFromSerializableClosure(SerializableClosure $source): Closure
    {
        return $source->getClosure();
    }

    protected function unserializeSourceFromSerializableClosureObject(SerializableClosureObject $source): Support\Components\Component | Support\Components\ViewComponent
    {
        // Clone the original object to prevent the reflection below to modify the properties on the original object, that might be reference elsewhere.
        $originalClone = clone $source->originalWithoutClosureProperties;

        $reflectionClass = new ReflectionClass($originalClone);

        foreach ($source->serializableClosureProperties as $serializablePropertyName => $serializableClosure) {
            $reflectionProperty = $reflectionClass->getProperty($serializablePropertyName);

            $value = clone $serializableClosure->getClosure();

            $reflectionFunction = new ReflectionFunction($value);

            $reflectionProperty->setValue(
                objectOrValue: $originalClone,
                // If the function is non-static, bind closure to current object instead of to a random serialized version of the object.
                value: $reflectionFunction->isStatic() ? $value : $value->bindTo($originalClone),
            );
        }

        foreach ($source->serializableClosureObjectProperties as $serializablePropertyName => $serializableClosureObject) {
            $reflectionProperty = $reflectionClass->getProperty($serializablePropertyName);

            $value = $this->unserializeSourceFromSerializableClosureObject($serializableClosureObject);

            $reflectionProperty->setValue(
                objectOrValue: $originalClone,
                value: $value,
            );
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->isInitialized($originalClone) && is_array(($reflectionPropertyValue = $reflectionProperty->getValue($originalClone)))) {
                $reflectionPropertyValue = Arr::map($reflectionPropertyValue, function (mixed $arrayValue) {
                    if ($arrayValue instanceof SerializableClosureObject) {
                        return $this->unserializeSourceFromSerializableClosureObject($arrayValue);
                    }

                    return $arrayValue;
                });

                $reflectionProperty->setValue($originalClone, $reflectionPropertyValue);
            }
        }

        return $originalClone;
    }

    protected function unserializeSourceFromSerializedEloquentQuery(SerializedEloquentQuery $source): Builder
    {
        return EloquentSerializeFacade::unserialize($source->source);
    }
}
