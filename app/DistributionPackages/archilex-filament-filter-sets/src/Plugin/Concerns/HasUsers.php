<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use App\Models\User;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasUsers
{
    use EvaluatesClosures;

    protected string | Closure $user = User::class;

    protected string | Closure $userTable = 'users';

    protected string | Closure $userTableKeyColumn = 'id';

    protected string | Closure $userTableNameColumn = 'name';

    public function user(string | Closure $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function userTable(string | Closure $table): static
    {
        $this->userTable = $table;

        return $this;
    }

    public function userTableKeyColumn(string | Closure $column): static
    {
        $this->userTableKeyColumn = $column;

        return $this;
    }

    public function userTableNameColumn(string | Closure $column): static
    {
        $this->userTableNameColumn = $column;

        return $this;
    }

    public function getUser(): string
    {
        return $this->evaluate($this->user);
    }

    public function getUserTable(): string
    {
        return $this->evaluate($this->userTable);
    }

    public function getUserTableKeyColumn(): string
    {
        return $this->evaluate($this->userTableKeyColumn);
    }

    public function getUserTableNameColumn(): string
    {
        return $this->evaluate($this->userTableNameColumn);
    }
}
