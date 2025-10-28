<x-layout>
    <x-slot:title>
        Seite nicht gefunden
    </x-slot>

    <div class="page-not-found">
        <h1>
            Die angeforderte Seite wurde nicht gefunden.
        </h1>

        <a class="button button--type-primary" href="{{ route('game-play.index') }}">
            Zur Übersicht
        </a>
    </div>
</x-layout>
