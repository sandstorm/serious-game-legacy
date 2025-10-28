@use(Domain\CoreGameLogic\GameId)
<x-layout>
    <x-slot:title>Neues Spiel</x-slot:title>
    <a class="button button--type-primary" href={{route('game-play.index')}}>Zurück zur Übersicht</a>
    <hr />
    <h2>Neues Spiel</h2>
    <form method="post" action={{ route('game-play.create-game') }}>
        @csrf
        <div class="form__group">
            <label for="numberOfPlayers">Anzahl Spieler:</label>
            <input class="form__textfield" type="number" id="numberOfPlayers" name="numberOfPlayers" required="required" min="2" max="4" />
        </div>

        <button type="submit" class="button button--type-primary">Spiel erstellen</button>
    </form>
</x-layout>
