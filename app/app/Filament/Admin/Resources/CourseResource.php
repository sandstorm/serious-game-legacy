<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CourseResource\Pages;
use App\Infolists\Components\GamesWithPlayers;
use App\Infolists\Components\PlayerTable;
use App\Models\Course;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    // --- Customize resource names here ---
    // The name in the navigation sidebar
    protected static ?string $navigationLabel = 'Kurse';
    // The singular name displayed on pages and in breadcrumbs
    protected static ?string $label = 'Kurs';
    // The plural name displayed in the sidebar and table headers
    protected static ?string $pluralModelLabel = 'Kurse';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Override the eloquent query for users with the role_lehrperson. They should only
     * see their own courses.
     * @return Builder<Course>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @phpstan-ignore disallowed.function */
        $user = request()->user();
        if ($user !== null && $user->role_lehrperson) {
            /** @phpstan-ignore argument.type */
            return parent::getEloquentQuery()->where('teacher_id', '=', $user->id);
        }
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        $isSuperAdminUser = function (?Course $record, Authenticatable $loggedInUser) {
            assert($loggedInUser instanceof User);

            return $loggedInUser->role_superadmin;
        };

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('teacher_id')
                    ->label('Lehrer:in')
                    ->relationship('teacher', 'name')
                    ->preload()
                    // only admins can change the teacher, so no teacher can remove themselves from their own course
                    ->visible($isSuperAdminUser),
                Forms\Components\Select::make('players')
                    ->label('Spieler:innen')
                    // email = ScoSciSurvey-ID
                    ->relationship('players', 'email')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Lehrer:in')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('players_count')
                    ->label('Spieler:innen')
                    ->counts('players')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('teacher.name')->label('Lehrer:in'),
                PlayerTable::make('players')->label('Spieler:innen in diesem Kurs'),
                GamesWithPlayers::make('games')->label('Spiele')
            ])->columns(2);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
