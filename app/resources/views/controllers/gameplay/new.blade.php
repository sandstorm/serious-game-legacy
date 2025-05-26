@use(Domain\CoreGameLogic\GameId)
<x-layout>
    <h2>Neues Spiel</h2>
    <form method="get" action={{ route('game-play.player-links', ['gameId' => GameId::random()->value]) }}>
        <div class="form__group">
            <label for="numberOfPlayers">Anzahl Spieler:</label>
            <x-form.textfield id="numberOfPlayers" name="numberOfPlayers" type="number" required="true" min="1" max="4" />
        </div>

        <x-form.submit>Spiel erstellen</x-form.submit>
    </form>
</x-layout>
