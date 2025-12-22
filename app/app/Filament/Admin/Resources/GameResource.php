<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GameResource\Pages;
use App\Infolists\Components\PlayerTable;
use App\Models\Game;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\GameId;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Str;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    // --- Customize resource names here ---
    // The name in the navigation sidebar
    protected static ?string $navigationLabel = 'Spiele';
    // The singular name displayed on pages and in breadcrumbs
    protected static ?string $label = 'Spiel';
    // The plural name displayed in the sidebar and table headers
    protected static ?string $pluralModelLabel = 'Spiele';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static function getLogs(Game $record, ForCoreGameLogic $coreGameLogic): string
    {
        $gameEvents = $coreGameLogic->getGameEvents(GameId::fromString($record['id']));
        $list = [];
        foreach ($gameEvents as $gameEvent) {
            $explodedEventName = explode('\\', $gameEvent::class);
            $eventName = $explodedEventName[array_key_last($explodedEventName)];
            $list[] = [
                "event" => $eventName,
                "data" => $gameEvent,
            ];
        }
        return json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Kurs')
                    ->searchable(),
                Tables\Columns\TextColumn::make('creator_name')
                    ->label('Erstellt von')
                    ->getStateUsing(fn (Game $record): string => $record->getCreatorName())
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('exportAsJson')
                    ->label(__('Export'))
                    ->action(function ($record, ForCoreGameLogic $coreGameLogic) {
                        $gameId = Str::slug($record->id, '_');
                        $course = Str::slug($record['course']?->name, '_');
                        $date = Str::slug($record[$record->getCreatedAtColumn()]);
                        return response()->streamDownload(function () use ($record, $coreGameLogic) {
                            echo self::getLogs($record, $coreGameLogic);
                        }, $date . "_" . $course . "_" . $gameId . '.json');
                    })
                    ->tooltip(__('Export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('id')->label('Spiel ID'),
                TextEntry::make('course.name')->label('Kurs'),
                PlayerTable::make('players')->label('Spieler:innen in diesem Spiel'),
            ])->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'view' => Pages\ViewGame::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
