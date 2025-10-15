<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources\CourseResource\Pages;

use App\Filament\Admin\Resources\CourseResource;
use App\Models\Course;
use App\Models\Game;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\GameId;
use Domain\CoreGameLogic\PlayerId;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Spieler:innen importieren')
                ->model('todo'),
            Action::make('Spiele erstellen')
                ->action(function (ForCoreGameLogic $coreGameLogic) {
                    /** @var Course $course */
                    $course = $this->record;

                    $allPlayers = $course->players;
                    $players = [];
                    foreach ($allPlayers as $player) {
                        // check if player is already in a game for this course
                        /** @phpstan-ignore staticMethod.dynamicCall, staticMethod.dynamicCall */
                        if ($player->games()->where('course_id', $course->id)->count() === 0) {
                            $players[] = $player;
                        }
                    }

                    $playersPerGame = 4;
                    $numberOfGames = (int) ceil(count($players) / $playersPerGame);
                    for ($i = 0; $i < $numberOfGames; $i++) {
                        $game = new Game();
                        /** @phpstan-ignore assign.propertyType */
                        $game->id = Str::ulid();
                        $game->course()->associate($course);
                        $game->save();
                        $game->players()->attach(array_slice($players, $i * $playersPerGame, $playersPerGame));
                        /** @phpstan-ignore method.nonObject */
                        $coreGameLogic->handle(GameId::fromString($game->id->toString()), StartPreGame::create(
                            numberOfPlayers: count($game->players)
                        )->withFixedPlayerIds(
                            ...array_map(fn($user) => PlayerId::fromString($user->email), $game->players->all())
                        ));
                    }
                    $this->redirect($this::getResource()::getUrl('view', ['record' => $course]));
                })
        ];
    }
}
