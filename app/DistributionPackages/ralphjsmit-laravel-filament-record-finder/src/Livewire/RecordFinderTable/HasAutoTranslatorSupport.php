<?php

namespace RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable;

use Countable;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;
use RalphJSmit\Filament\AutoTranslator\Enums\PageTranslationContext;

trait HasAutoTranslatorSupport
{
    /**
     * The configuration necessary to determine where to forward the translation calls to
     * is only received as a public property. The `HasTranslations` interface requires
     * a static method call, hence it doesn't have access to the public properties.
     * However, we know beforehand that the static `getTranslation()` is never
     * called without the `$livewire` being instantiated. Hence, we'll set
     * the important configuration values as static properties on boot.
     */
    public static bool $isStandalone;

    /**
     * @var null|HasTranslations<resource|HasTranslations>
     */
    public static ?string $tableQueryModelResource;

    public static function getTranslation(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false, ?PageTranslationContext $pageTranslationContext = null): mixed
    {
        if (static::$isStandalone || ! is_a(static::$tableQueryModelResource, HasTranslations::class, true)) {
            // Even if we return a the key when no resource, then it will produce weird translations that don't
            // make sense (like "columns.{name}.label" as global translation). Therefore we will return `null`.
            return null;
            // return $allowNull ? null : $key;
        }

        return static::$tableQueryModelResource::getTranslation($key, $replace, $number, $allowNull, $pageTranslationContext);
    }

    public function bootHasAutoTranslatorSupport(): void
    {
        static::$isStandalone = $this->getRecordFinderTableIsStandalone();
        static::$tableQueryModelResource = $this->getRecordFinderTableQueryModelResource();
    }
}
