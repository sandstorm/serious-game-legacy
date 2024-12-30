<?php

namespace Archilex\AdvancedTables\Components;

use Archilex\AdvancedTables\Components\Concerns\CanBeDefault;
use Archilex\AdvancedTables\Components\Concerns\CanBeFavorited;
use Archilex\AdvancedTables\Components\Concerns\CanBeHidden;
use Archilex\AdvancedTables\Components\Concerns\CanPreserve;
use Archilex\AdvancedTables\Components\Concerns\HasBadge;
use Archilex\AdvancedTables\Components\Concerns\HasDefaults;
use Archilex\AdvancedTables\Components\Concerns\HasIcon;
use Archilex\AdvancedTables\Components\Concerns\HasIndicator;
use Archilex\AdvancedTables\Components\Concerns\HasLabel;
use Archilex\AdvancedTables\Components\Concerns\HasTooltip;
use Closure;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\HasColor;
use Illuminate\Database\Eloquent\Builder;

class PresetView extends Component
{
    use CanBeDefault;
    use CanBeFavorited;
    use CanBeHidden;
    use CanPreserve;
    use HasBadge;
    use HasColor;
    use HasDefaults;
    use HasIcon;
    use HasIndicator;
    use HasLabel;
    use HasTooltip;

    protected ?int $managedByCurrentUserId = null;

    protected bool $isManagedByCurrentUser = false;

    protected bool $isFavoritedByCurrentUser = false;

    protected ?int $managedByCurrentUserSortOrder = null;

    protected ?Closure $modifyQueryUsing = null;

    public function __construct(string | Closure | null $label = null)
    {
        $this->label($label);
    }

    public static function make(string | Closure | null $label = null): static
    {
        $static = app(static::class, ['label' => $label]);
        $static->configure();

        return $static;
    }

    public function managedByCurrentUserId(int $id): static
    {
        $this->managedByCurrentUserId = $id;

        return $this;
    }

    public function managedByCurrentUser(bool $condition = true): static
    {
        $this->isManagedByCurrentUser = $condition;

        return $this;
    }

    public function favoritedByCurrentUser(bool $condition = true): static
    {
        $this->isFavoritedByCurrentUser = $condition;

        return $this;
    }

    public function managedByCurrentUserSortOrder(int $order): static
    {
        $this->managedByCurrentUserSortOrder = $order;

        return $this;
    }

    public function query(Closure $callback): static
    {
        $this->modifyQueryUsing($callback);

        return $this;
    }

    public function modifyQuery(Builder $query): Builder
    {
        return $this->evaluate($this->modifyQueryUsing, [
            'query' => $query,
        ]) ?? $query;
    }

    public function modifyQueryUsing(Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function modifiesQuery(): bool
    {
        return (bool) $this->modifyQueryUsing;
    }

    public function isManagedByCurrentUser(): bool
    {
        return (bool) $this->isManagedByCurrentUser;
    }

    public function isFavoritedByCurrentUser(): bool
    {
        return (bool) $this->isFavoritedByCurrentUser;
    }

    public function getManagedByCurrentUserSortOrder(): int
    {
        return (int) $this->managedByCurrentUserSortOrder;
    }

    public function getManagedByCurrentUserId(): int
    {
        return (int) $this->managedByCurrentUserId;
    }
}
