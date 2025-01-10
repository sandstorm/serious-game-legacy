<?php

declare(strict_types=1);

namespace App\Livewire\Pulse;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Livewire\Attributes\Lazy;
use Laravel\Pulse\Livewire\Card;

#[Lazy]
class UnknownPermissions extends Card
{
    public function render(Connection $connection)
    {
        $unknownPermissions = $connection
            ->table('unknown_permissions')
            ->select('*')
            ->orderBy('count', 'desc')
            ->limit(101)
            ->get()
            ->map(function ($item) {
                $item->last_seen = Carbon::parse($item->last_seen);
                return $item;
            });

        return view('livewire.pulse.unknown-permissions', [
            'unknownPermissions' => $unknownPermissions
        ]);
    }

    public function removeUnknownPermission(int $id, Connection $connection)
    {
        $connection->table('unknown_permissions')
            ->delete($id);
    }
}
