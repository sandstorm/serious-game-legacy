@props([
    'games' => [],
    'player' => null,
])

<x-layout>
    <x-slot:title>Willkommen</x-slot:title>
    <x-slot:footer>
        <x-footer.footer />
    </x-slot:footer>
    <x-header.header title="Spielübersicht" />

    @if($player->can_create_games)
        <a type="button" class="button button--type-primary" href={{ @route("game-play.new-game") }}>Neues Spiel erstellen</a>
        <hr />
    @endif

    <x-games.games-list :games="$games" :player="$player" />
</x-layout>
