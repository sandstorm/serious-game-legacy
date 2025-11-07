<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\CourseResource\Pages\ViewCourse;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
});

describe('create random game groups', function () {
    it('returns an empty array if there are not enough players', function () {
        /** @var testcase $this */
        // test with one player
        $playerGroups = new Collection([
            User::factory()->count(1)->make(),
        ]);
        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect($actualGameGroups)->toBe([]);

        // test with no players
        $playerGroups = new Collection([]);
        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect($actualGameGroups)->toBe([]);
    });

    it('returns an array with one group of two players for two players', function () {
        /** @var testcase $this */

        [$player1, $player2] = User::factory()->count(2)->make();
        $playerGroups = new Collection([
            $player1,
            $player2,
        ]);
        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect(count($actualGameGroups))->toBe(1, "actualGameGroups should containt 1 game group")
            ->and(count($actualGameGroups[0]))->toBe(2, "Game group should contain 2 players");
        $actualPlayerIds = array_map(fn($player) => $player->name, $actualGameGroups[0]);
        expect($actualPlayerIds)->toContainEqual($player1->name, $player2->name);
    });

    it('returns one group with 2 and one with 3 player, when 5 players are in a course', function () {
        /** @var testcase $this */
        $players = User::factory()->count(5)->make();
        $playerGroups = new Collection($players);

        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect(count($actualGameGroups))->toBe(2, "actualGameGroups should containt 2 game groups");

        $actualGroupSizes = array_map(fn($group) => count($group), $actualGameGroups);
        expect($actualGroupSizes)->toContain(2, 3);
    });

    it('works for exactly 4 players in a course', function () {
        /** @var testcase $this */
        $players = User::factory()->count(4)->make();
        $playerGroups = new Collection($players);

        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect(count($actualGameGroups))->toBe(1, "actualGameGroups should containt 4 game groups");

        $actualGroupSizes = array_map(fn($group) => count($group), $actualGameGroups);
        expect($actualGroupSizes)->toEqual([4]);
    });

    it('works for course numbers divisible by 4', function () {
        /** @var testcase $this */
        $players = User::factory()->count(16)->make();
        $playerGroups = new Collection($players);

        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect(count($actualGameGroups))->toBe(4, "actualGameGroups should containt 4 game groups");

        $actualGroupSizes = array_map(fn($group) => count($group), $actualGameGroups);
        expect($actualGroupSizes)->toEqual([4, 4, 4, 4]);
    });

    it('works for course numbers divisible by 4 with a rest of 1', function () {
        /** @var testcase $this */
        $players = User::factory()->count(17)->make();
        $playerGroups = new Collection($players);

        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect(count($actualGameGroups))->toBe(5, "actualGameGroups should containt 4 game groups");

        $actualGroupSizes = array_map(fn($group) => count($group), $actualGameGroups);
        expect($actualGroupSizes)->toEqual([3, 2, 4, 4, 4]);
    });


    it('assigns every player to a game', function () {
        /** @var testcase $this */
        $players = User::factory()->count(17)->make();
        $expectedPlayersInGroups = [];
        foreach ($players as $player) {
            $expectedPlayersInGroups[] = $player->name;
        }
        $playerGroups = new Collection($players);

        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        $actualPlayersInGroups = [];
        foreach ($actualGameGroups as $group) {
            foreach ($group as $player) {
                $actualPlayersInGroups[] = $player->name;
            };
        };

        expect($actualPlayersInGroups)->toHaveCount(17)
            ->and($actualPlayersInGroups)->toContain(...$expectedPlayersInGroups);
    });
});
