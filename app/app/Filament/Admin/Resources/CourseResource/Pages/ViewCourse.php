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
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

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

                    $allPlayers = $course->players;
                    $players = [];
                    // TODO players who created their own game are not included at this point
                    foreach ($allPlayers as $player) {
                        // check if player is already in a game for this course
                        /** @phpstan-ignore staticMethod.dynamicCall, staticMethod.dynamicCall */
                        if ($player->games()->where('course_id', $course->id)->count() === 0) {
                            $players[] = $player;
                        }
                    }

                    // current user is the creator of the games
                    $panel = Filament::getCurrentPanel();
                    $user = $panel?->auth()->user();

                    $playersPerGame = 4;
                    $numberOfGames = (int) ceil(count($players) / $playersPerGame);
                    for ($i = 0; $i < $numberOfGames; $i++) {
                        $game = new Game();
                        /** @phpstan-ignore assign.propertyType */
                        $game->id = Str::ulid();
                        $game->course()->associate($course);
                        $game->creator()->associate($user); // no creator
                        $game->save();
                        $game->players()->attach(array_slice($players, $i * $playersPerGame, $playersPerGame));
                        /** @phpstan-ignore method.nonObject */
                        $coreGameLogic->handle(GameId::fromString($game->id->toString()), StartPreGame::create(
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
