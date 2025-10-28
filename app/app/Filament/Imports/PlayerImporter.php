<?php
declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Course;
use App\Models\Player;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PlayerImporter extends Importer
{
    protected static ?string $model = Player::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->label('SoSciSurvey ID')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('password')
                ->label('Passwort')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
        ];
    }

    /**
     * @return Player
     */
    public function resolveRecord(): Player
    {
        /** @var Player $player */
        $player = Player::firstOrNew([
            'email' => $this->data['email'],
        ]);

        return $player;
    }

    /**
     * Runs after a the player is saved in the database.
     * Attaches the player to the course.
     *
     * @return void
     */
    protected function afterSave(): void
    {
        /** @var Course $course */
        $course = $this->options['course'];

        /** @var Player $player */
        $player = $this->record;

        // only if not already attached
        /** @phpstan-ignore-next-line */
        if ($player->courses()->where('course_id', $course->id)->exists()) {
            return;
        }

        $player->courses()->attach($course);
        $player->save();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import abgeschlossen: ' . number_format($import->successful_rows) . 'Spieler:innen Importiert.';

        $failedRowsCount = $import->getFailedRowsCount();
        if ($failedRowsCount > 0) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('Zeile')->plural($failedRowsCount) . ' fehlgeschlagen.';
        }

        return $body;
    }
}
