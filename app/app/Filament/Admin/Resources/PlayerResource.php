<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlayerResource\Pages;
use App\Models\Player;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    // --- Customize resource names here ---
    // The name in the navigation sidebar
    protected static ?string $navigationLabel = 'Spieler:innen';
    // The singular name displayed on pages and in breadcrumbs
    protected static ?string $label = 'Spieler:in';
    // The plural name displayed in the sidebar and table headers
    protected static ?string $pluralModelLabel = 'Spieler:innen';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // we use email of the user model for the representation of the SoSciSurvey ID
                Forms\Components\TextInput::make('email')
                    ->label('SoSciSurvey ID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Passwort')
                    ->password()
                    ->maxLength(255)
                    // only update password when a new password is set
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                        $component->state('');
                    })
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\Toggle::make('can_create_games')
                    ->label('Kann Spiele erstellen')
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
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('SoSciSurvey ID')
                    ->searchable()
                    ->toggleable(),
                // bool flag for 'can_create_games'
                Tables\Columns\IconColumn::make('can_create_games')
                    ->label('Kann Spiele erstellen')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
        ];
    }
}
