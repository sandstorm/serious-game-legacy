<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CourseResource\Pages;

use App\Filament\Admin\Resources\CourseResource;
use App\Filament\Imports\PlayerImporter;
use App\Models\Course;
use App\Models\Game;
use App\Models\Player;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * @return int[] Array of group sizes (e.g. [4, 4, 3] for 11 players preferring 4er groups)
     */
    private static function calculateGroupSizes(int $n, int $preferGroupsOf): array
    {
        if ($n < 2) {
            return [];
        }
        if ($n <= 4) {
            return [$n];
        }
        if ($n === 5) {
            return [3, 2];
        }

        if ($preferGroupsOf === 3) {
            return self::calculateGroupSizesPrefer3($n);
        }

        return self::calculateGroupSizesPrefer4($n);
    }

    /**
     * @return int[]
     */
    private static function calculateGroupSizesPrefer4(int $n): array
    {
        $remainder = $n % 4;

        if ($remainder === 0) {
            return array_fill(0, intdiv($n, 4), 4);
        }

        if ($remainder === 3) {
            return [...array_fill(0, intdiv($n, 4), 4), 3];
        }

        if ($remainder === 2) {
            return [...array_fill(0, intdiv($n - 6, 4), 4), 3, 3];
        }

        // remainder === 1: subtract 9 (three 3er groups), rest fills with 4er
        return [...array_fill(0, intdiv($n - 9, 4), 4), 3, 3, 3];
    }

    /**
     * @return int[]
     */
    private static function calculateGroupSizesPrefer3(int $n): array
    {
        $remainder = $n % 3;

        if ($remainder === 0) {
            return array_fill(0, intdiv($n, 3), 3);
        }

        if ($remainder === 1) {
            // one 4er group, rest 3er
            return [...array_fill(0, intdiv($n - 4, 3), 3), 4];
        }

        // remainder === 2: two 4er groups, rest 3er
        return [...array_fill(0, intdiv($n - 8, 3), 3), 4, 4];
    }

    /**
     * @param Collection<int, Player> $playersCollection
     * @return array<array<Player>>
     */
    private static function createRandomGameGroups(Collection $playersCollection, int $preferGroupsOf = 4): array
    {
        $allPlayers = [];

        foreach ($playersCollection as $player) {
            $allPlayers[] = $player;
        }

        $groupSizes = self::calculateGroupSizes(count($allPlayers), $preferGroupsOf);

        if ($groupSizes === []) {
            return [];
        }

        shuffle($allPlayers);

        $gameGroups = [];
        $offset = 0;
        foreach ($groupSizes as $size) {
            $gameGroups[] = array_slice($allPlayers, $offset, $size);
            $offset += $size;
        }

        return $gameGroups;
    }

    /**
     * @return int[]
     */
    public static function calculateGroupSizesForTesting(int $n, int $preferGroupsOf): array
    {
        return self::calculateGroupSizes($n, $preferGroupsOf);
    }

    /**
     * Proxy method to test the private function `createRandomGameGroups`.
     * Calling `...ForTesting` functions outside of test code will be caught by phpstan.
     * @param Collection<int, Player> $playersCollection
     * @return array<array<Player>>
     */
    public static function createRandomGameGroupsForTesting(Collection $playersCollection, int $preferGroupsOf = 4): array
    {
        return self::createRandomGameGroups($playersCollection, $preferGroupsOf);
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Spieler:innen importieren')
                ->importer(PlayerImporter::class)
                ->csvDelimiter(';')
                ->options([
                    'course' => $this->record
                ]),
            Action::make('Spiele erstellen')
                ->form([
                    Select::make('preferGroupsOf')
                        ->label('Gruppengröße bevorzugen')
                        ->options([
                            4 => 'Bevorzugt 4er-Gruppen',
                            3 => 'Bevorzugt 3er-Gruppen',
                        ])
                        ->default(4)
                        ->required(),
                ])
                ->action(function (array $data, ForCoreGameLogic $coreGameLogic) {

                    /** @var Course $course */
                    $course = $this->record;
                    // current user is the creator of the games
                    $panel = Filament::getCurrentPanel();
                    $user = $panel?->auth()->user();

                    $gameGroups = self::createRandomGameGroups($course->players, (int) $data['preferGroupsOf']);

                    if (count($gameGroups) === 0) {
                        Notification::make()
                            ->title('Es werden mindestens zwei Spielende benötigt, um Spiele zu erstellen')
                            ->warning()
                            ->send();
                        return;
                    }

                    foreach ($gameGroups as $group) {
                        $game = new Game();
                        /** @phpstan-ignore assign.propertyType */
                        $game->id = Str::ulid();
                        $game->course()->associate($course);
                        $game->creator()->associate($user); // no creator
                        $game->save();
                        $game->players()->attach($group);
                        $coreGameLogic->handle(GameId::fromString((string) $game->id), StartPreGame::create(
                            numberOfPlayers: count($game->players)
                        )->withFixedPlayerIds(
                            /** @phpstan-ignore argument.type */
                            array_map(fn (Player $user) => PlayerId::fromString($user->id), $game->players->all())
                        ));
                    }
                    $this->redirect($this::getResource()::getUrl('view', ['record' => $course]));
                })
        ];
    }
}
