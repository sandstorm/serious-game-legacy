<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;

trait HasTenancy
{
    protected bool | Closure $tenancyIsEnabled = true;

    protected ?string $tenantModel = null;

    protected ?string $tenantColumn = 'tenant_id';

    /**
     * @deprecated Use `scopeToTenancy()` instead.
     */
    public function tenancyEnabled(bool | Closure $condition = true): static
    {
        $this->scopeToTenancy($condition);

        return $this;
    }

    public function scopeToTenancy(bool | Closure $condition = true): static
    {
        $this->tenancyIsEnabled = $condition;

        return $this;
    }

    public function tenant(string $tenantModel): static
    {
        $this->tenantModel = $tenantModel;

        return $this;
    }

    public function tenantColumn(string $column): static
    {
        $this->tenantColumn = $column;

        return $this;
    }

    public function tenancyIsEnabled(): bool
    {
        return $this->evaluate($this->tenancyIsEnabled);
    }

    public function getTenantModel(): ?string
    {
        return $this->tenantModel;
    }

    public function getTenantColumn(): ?string
    {
        return $this->tenantColumn;
    }
}
