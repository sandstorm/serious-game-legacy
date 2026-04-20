<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\CourseResource\Pages\ViewCourse;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
});

describe('calculateGroupSizes', function () {
    describe('prefer 4er groups', function () {
        it('returns empty for less than 2 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(0, 4))->toBe([])
                ->and(ViewCourse::calculateGroupSizesForTesting(1, 4))->toBe([]);
        });

        it('returns correct sizes for small groups', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(2, 4))->toBe([2])
                ->and(ViewCourse::calculateGroupSizesForTesting(3, 4))->toBe([3])
                ->and(ViewCourse::calculateGroupSizesForTesting(4, 4))->toBe([4]);
        });

        it('returns [3, 2] for 5 players (only case with a 2er group)', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(5, 4))->toBe([3, 2]);
        });

        it('returns [3, 3] for 6 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(6, 4))->toBe([3, 3]);
        });

        it('returns [4, 3] for 7 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(7, 4))->toBe([4, 3]);
        });

        it('returns [4, 4] for 8 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(8, 4))->toBe([4, 4]);
        });

        it('returns [3, 3, 3] for 9 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(9, 4))->toBe([3, 3, 3]);
        });

        it('returns [4, 3, 3] for 10 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(10, 4))->toBe([4, 3, 3]);
        });

        it('returns all 4er for multiples of 4', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(12, 4))->toBe([4, 4, 4])
                ->and(ViewCourse::calculateGroupSizesForTesting(16, 4))->toBe([4, 4, 4, 4])
                ->and(ViewCourse::calculateGroupSizesForTesting(20, 4))->toBe([4, 4, 4, 4, 4]);
        });

        it('never creates 2er groups except for n=5', function () {
            for ($n = 6; $n <= 30; $n++) {
                $sizes = ViewCourse::calculateGroupSizesForTesting($n, 4);
                foreach ($sizes as $size) {
                    expect($size)->toBeGreaterThanOrEqual(3, "n=$n produced a group of size $size");
                }
                expect(array_sum($sizes))->toBe($n, "n=$n: sum of group sizes doesn't match");
            }
        });
    });

    describe('prefer 3er groups', function () {
        it('returns empty for less than 2 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(0, 3))->toBe([])
                ->and(ViewCourse::calculateGroupSizesForTesting(1, 3))->toBe([]);
        });

        it('returns correct sizes for small groups', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(2, 3))->toBe([2])
                ->and(ViewCourse::calculateGroupSizesForTesting(3, 3))->toBe([3])
                ->and(ViewCourse::calculateGroupSizesForTesting(4, 3))->toBe([4]);
        });

        it('returns [3, 2] for 5 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(5, 3))->toBe([3, 2]);
        });

        it('returns [3, 3] for 6 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(6, 3))->toBe([3, 3]);
        });

        it('returns [3, 4] for 7 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(7, 3))->toBe([3, 4]);
        });

        it('returns [4, 4] for 8 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(8, 3))->toBe([4, 4]);
        });

        it('returns all 3er for multiples of 3', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(9, 3))->toBe([3, 3, 3])
                ->and(ViewCourse::calculateGroupSizesForTesting(12, 3))->toBe([3, 3, 3, 3])
                ->and(ViewCourse::calculateGroupSizesForTesting(15, 3))->toBe([3, 3, 3, 3, 3]);
        });

        it('returns [3, 3, 4] for 10 players', function () {
            expect(ViewCourse::calculateGroupSizesForTesting(10, 3))->toBe([3, 3, 4]);
        });

        it('never creates 2er groups except for n=5', function () {
            for ($n = 6; $n <= 30; $n++) {
                $sizes = ViewCourse::calculateGroupSizesForTesting($n, 3);
                foreach ($sizes as $size) {
                    expect($size)->toBeGreaterThanOrEqual(3, "n=$n produced a group of size $size");
                }
                expect(array_sum($sizes))->toBe($n, "n=$n: sum of group sizes doesn't match");
            }
        });
    });
});

describe('createRandomGameGroups', function () {
    it('returns an empty array if there are not enough players', function () {
        $playerGroups = new Collection([User::factory()->count(1)->make()]);
        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect($actualGameGroups)->toBe([]);

        $playerGroups = new Collection([]);
        $actualGameGroups = ViewCourse::createRandomGameGroupsForTesting($playerGroups);
        expect($actualGameGroups)->toBe([]);
    });

    it('assigns every player to a game', function () {
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
            }
        }

        expect($actualPlayersInGroups)->toHaveCount(17)
            ->and($actualPlayersInGroups)->toContain(...$expectedPlayersInGroups);
    });

    it('respects preferGroupsOf parameter', function () {
        $players = User::factory()->count(12)->make();
        $playerGroups = new Collection($players);

        $groupsPrefer4 = ViewCourse::createRandomGameGroupsForTesting($playerGroups, 4);
        $sizesPrefer4 = array_map(fn ($group) => count($group), $groupsPrefer4);
        sort($sizesPrefer4);
        expect($sizesPrefer4)->toBe([4, 4, 4]);

        $groupsPrefer3 = ViewCourse::createRandomGameGroupsForTesting($playerGroups, 3);
        $sizesPrefer3 = array_map(fn ($group) => count($group), $groupsPrefer3);
        sort($sizesPrefer3);
        expect($sizesPrefer3)->toBe([3, 3, 3, 3]);
    });
});
