<?php

declare(strict_types=1);

namespace App\Livewire\Pulse;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\View\View;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class UnknownPermissions extends Card
{
    public function render(Connection $connection): View
    {
        $unknownPermissions = $connection
            ->table('unknown_permissions')
            ->select('*')
            ->orderBy('count', 'desc')
            ->limit(101)
            ->get()
            ->map(function ($item) {
                $item->last_seen = is_string($item->last_seen) ? Carbon::parse($item->last_seen) : null;

                return $item;
            });

        return view('livewire.pulse.unknown-permissions', [
            'unknownPermissions' => $unknownPermissions,
        ]);
    }

    public function removeUnknownPermission(int $id, Connection $connection): void
    {
        $connection->table('unknown_permissions')
            ->delete($id);
    }
}
