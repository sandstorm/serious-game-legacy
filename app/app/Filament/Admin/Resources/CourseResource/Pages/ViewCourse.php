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
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * @param Collection<int, Player> $playersCollection
     * @return array<array<Player>>
     */
    private static function createRandomGameGroups(Collection $playersCollection): array
    {
        $allPlayers = [];

        foreach ($playersCollection as $player) {
            $allPlayers[] = $player;
        }

        // We need at least two players
        if (count($allPlayers) < 2) {
            return [];
        }

        // we want random groups, so we shuffle all players before slicing the array into groups
        shuffle($allPlayers);

        $gameGroups = [];

        /**
         * WHY:
         * We need at least 2 players in a game. If we fill all games with four players we may end up
         * with one group that only has 1 player. That is the case when playerCount % 4 === 1. In that
         * case we can simply create one group with 2 players and one group with 3 players. The remaining
         * players can be put in full groups.
         */
        if (count($allPlayers) > 4 && count($allPlayers) % 4 === 1) {
            $gameGroups[] = array_slice($allPlayers, 0, 3);
            $gameGroups[] = array_slice($allPlayers, 3, 2);
            $allPlayers = array_slice($allPlayers, 5);
        }

        /**
         * Fill the remaining games with up to 4 players (unless there are less than 4 left)
         */
        $playersPerGame = 4;
        $numberOfGames = (int) ceil(count($allPlayers) / $playersPerGame);
        for ($i = 0; $i < $numberOfGames; $i++) {
            $gameGroups[] = array_slice($allPlayers, $i * $playersPerGame, $playersPerGame);
        }

        return $gameGroups;
    }

    /**
     * Proxy method to test the private function `createRandomGameGroups`.
     * Calling `...ForTesting` functions outside of test code will be caught by phpstan.
     * @param Collection<int, Player> $playersCollection
     * @return array<array<Player>>
     */
    public static function createRandomGameGroupsForTesting(Collection $playersCollection): array
    {
        return self::createRandomGameGroups($playersCollection);
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
                ->action(function (ForCoreGameLogic $coreGameLogic) {

                    /** @var Course $course */
                    $course = $this->record;
                    // current user is the creator of the games
                    $panel = Filament::getCurrentPanel();
                    $user = $panel?->auth()->user();

                    $gameGroups = self::createRandomGameGroups($course->players);

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
                            array_map(fn(Player $user) => PlayerId::fromString($user->id), $game->players->all())
                        ));
                    }
                    $this->redirect($this::getResource()::getUrl('view', ['record' => $course]));
                })
        ];
    }
}
