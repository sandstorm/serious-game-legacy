@use(Domain\CoreGameLogic\GameId)
<x-layout>
    <h2>Neues Spiel</h2>
    <form method="get" action={{ route('game-play.player-links', ['gameId' => GameId::random()->value]) }}>
        <div class="form__group">
            <label for="numberOfPlayers">Anzahl Spieler:</label>
            <input class="form__textfield" type="number" id="numberOfPlayers" name="numberOfPlayers" required="required" min="1" max="4" />
        </div>

        <button type="submit" class="button button--type-primary">Spiel erstellen</button>
    </form>
</x-layout>
