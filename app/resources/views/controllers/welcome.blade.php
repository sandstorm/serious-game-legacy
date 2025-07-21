<x-layout>
    <h1>Willkommen beim Serious Game</h1>
    <a type="button" class="button button--type-primary" href={{ @route("game-play.new-game") }}>Neues Spiel</a>
    <a type="button" class="button button--type-primary" href={{ @route("game-play.quick-start", ['players' => 2]) }}>Quick Start (2 player)</a>
    <a type="button" class="button button--type-primary" href={{ @route("game-play.quick-start", ['players' => 3]) }}>Quick Start (3 player)</a>
    <a type="button" class="button button--type-primary" href={{ @route("game-play.quick-start", ['players' => 4]) }}>Quick Start (4 player)</a>
</x-layout>
