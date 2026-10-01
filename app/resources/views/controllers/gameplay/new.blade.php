@use('Domain\Definitions\Configuration\Configuration')

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
        <input type="hidden" id="numberOfPlayers" name="numberOfPlayers" required="required" min="{{ Configuration::MIN_NUMBER_OF_PLAYERS }}" max="{{ Configuration::MAX_NUMBER_OF_PLAYERS }}" :value="amountOfPlayers" />

        <h1>Anzahl der Spielenden wählen</h1>

        <div class="create-game__players">
            @for ($numberOfPlayers = Configuration::MIN_NUMBER_OF_PLAYERS; $numberOfPlayers <= Configuration::MAX_NUMBER_OF_PLAYERS; $numberOfPlayers++)
                <button
                    type="button"
                    class="button button--type-icon"
                    :class="amountOfPlayers === {{ $numberOfPlayers }} ? 'button--type-primary' : 'button--type-secondary'"
                    title="{{ $numberOfPlayers }} Spieler:innen"
                    x-on:click="amountOfPlayers = {{ $numberOfPlayers }}"
                >
                    {{ $numberOfPlayers }}
                </button>
            @endfor
        </div>

        <button
            type="submit"
            class="button button--type-primary"
            :disabled="amountOfPlayers < {{ Configuration::MIN_NUMBER_OF_PLAYERS }}"
        >
            Weiter
        </button>
    </form>
</x-layout>
