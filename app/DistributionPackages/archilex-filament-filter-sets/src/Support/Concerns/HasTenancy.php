<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Facades\Filament;

trait HasTenancy
{
    public static function tenancyIsEnabled(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->tenancyIsEnabled();
        }

        return config('advanced-tables.tenancy.enabled', true);
    }

    public static function getTenantId(): ?string
    {
        if (! self::hasTenancy()) {
            return null;
        }

        $tenant = app(self::getTenant());

        if (method_exists($tenant, 'getTenantId')) {
            return $tenant->getTenantId();
        }

        if (Filament::hasTenancy()) {
            return Filament::getTenant()?->id;
        }

        if ($tenant instanceof \Spatie\Multitenancy\Models\Tenant) {
            return $tenant::current()->id;
        }

        if ($tenant instanceof \Stancl\Tenancy\Contracts\Tenant) {
            return $tenant->id;
        }

        return null;
    }

    public static function getTenant(): ?string
    {
        if (! self::hasTenancy()) {
            return null;
        }

        if ($tenant = config('advanced-tables.tenancy.tenant', null)) {
            return $tenant;
        }

        if (Filament::hasTenancy()) {
            return Filament::getTenantModel();
        }

        return AdvancedTablesPlugin::get()->getTenantModel();
    }

    public static function getTenantColumn(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getTenantColumn();
        }

        return config('advanced-tables.tenancy.tenant_column', 'tenant_id');
    }

    public static function hasTenancy(): bool
    {
        if (! self::tenancyIsEnabled()) {
            return false;
        }

        if (config('advanced-tables.tenancy.tenant', null)) {
            return true;
        }

        if (! self::pluginRegistered()) {
            return false;
        }

        if (Filament::hasTenancy()) {
            return true;
        }

        return filled(AdvancedTablesPlugin::get()->getTenantModel());
    }
}
