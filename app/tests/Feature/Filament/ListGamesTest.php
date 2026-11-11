<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\GameResource\Pages\ListGames;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->admin = User::factory()->create(['name' => 'Admin Person', 'role_superadmin' => true]);
    $this->actingAs($this->admin);

    $this->gameByAdmin = new Game();
    $this->gameByAdmin->forceFill(['creator_id' => $this->admin->id])->save();

    $player = Player::create(['email' => 'player-creator', 'password' => 'password']);
    $player->forceFill(['name' => 'Creating Player'])->save();
    $this->gameByPlayer = new Game();
    $this->gameByPlayer->forceFill(['creator_id' => $player->id])->save();
});

describe('search in the games table', function () {
    it('finds games by the name of the creating admin', function () {
        Livewire::test(ListGames::class)
            ->searchTable('Admin')
            ->assertCanSeeTableRecords([$this->gameByAdmin])
            ->assertCanNotSeeTableRecords([$this->gameByPlayer]);
    });

    it('finds games by the name of the creating player', function () {
        Livewire::test(ListGames::class)
            ->searchTable('Creating')
            ->assertCanSeeTableRecords([$this->gameByPlayer])
            ->assertCanNotSeeTableRecords([$this->gameByAdmin]);
    });
});
