<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use App\Models\User;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Illuminate\Contracts\Auth\Guard;

trait HasUsers
{
    public static function getUser(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getUser();
        }

        return config('advanced-tables.users.user', User::class);
    }

    /**
     * @deprecated Use `getUser()` instead.
     */
    public static function getUserModelName(): string
    {
        return self::getUser();
    }

    public static function getUserTable(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getUserTable();
        }

        return config('advanced-tables.users.user_table', 'users');
    }

    public static function getUserTableKeyColumn(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getUserTableKeyColumn();
        }

        return config('advanced-tables.users.user_table_key_column', 'id');
    }

    public static function getUserTableNameColumn(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getUserTableNameColumn();
        }

        return config('advanced-tables.users.user_table_name_column', 'name');
    }

    public static function auth(): Guard
    {
        if (filament()->isServing()) {
            return filament()->auth();
        }

        return auth()->guard(config('advanced-tables.users.auth_guard', null));
    }
}
