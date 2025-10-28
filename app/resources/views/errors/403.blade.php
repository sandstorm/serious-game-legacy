<x-layout>
    <x-slot:title>
        Keine Berechtigung
    </x-slot>

    <div class="page-not-found">
        <h1>
            Du hast keine Berechtigung, diese Seite zu sehen.
        </h1>

        <a class="button button--type-primary" href="{{ route('game-play.index') }}">
            Zur Übersicht
        </a>
    </div>
</x-layout>
