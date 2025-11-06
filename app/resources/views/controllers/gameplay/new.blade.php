<x-layout>
    <x-slot:title>Neues Spiel erstellen</x-slot:title>

    <header class="game-header">
        <a class="button button--type-text" href={{route('game-play.index')}}>Zurück zur Übersicht</a>
    </header>

    <form
        x-data="{ amountOfPlayers: 0 }"
        method="post"
        class="create-game"
        action={{ route('game-play.create-game') }}
    >
        @csrf
        <input type="hidden" id="numberOfPlayers" name="numberOfPlayers" required="required" min="2" max="4" :value="amountOfPlayers" />

        <h1>Anzahl der Spielenden wählen</h1>

        <div class="create-game__players">
            <button
                type="button"
                class="button button--type-icon"
                :class="amountOfPlayers === 2 ? 'button--type-primary' : 'button--type-secondary'"
                title="2 Spieler:innen"
                x-on:click="amountOfPlayers = 2"
            >
                2
            </button>
            <button
                type="button"
                class="button button--type-icon"
                :class="amountOfPlayers === 3 ? 'button--type-primary' : 'button--type-secondary'"
                title="2 Spieler:innen"
                x-on:click="amountOfPlayers = 3"
            >
                3
            </button>
            <button
                type="button"
                class="button button--type-icon"
                :class="amountOfPlayers === 4 ? 'button--type-primary' : 'button--type-secondary'"
                title="2 Spieler:innen"
                x-on:click="amountOfPlayers = 4"
            >
                4
            </button>
        </div>

        <button
            type="submit"
            class="button button--type-primary"
            :disabled="amountOfPlayers < 2"
        >
            Weiter
        </button>
    </form>
</x-layout>
