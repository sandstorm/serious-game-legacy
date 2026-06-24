@extends ('components.modal.modal', ['closeModal' => "toggleSidebarMenu()", "size" => "small"])

@section('title')
    Menü
@endsection

@section('content')
    <nav>
        <ul>
            <li>
                <a href={{route('game-play.index')}}>Zurück zur Übersicht</a>
            </li>
            <li>
                <a href="https://www.legacy-finanzbildung.de/spielanleitung/" target="_blank">Spielregeln</a>
            </li>
            <li>
                <a href="https://www.legacy-finanzbildung.de/sonstiges/kontakt/" target="_blank">Kontakt</a>
            </li>
            <li>
                <a href="https://www.legacy-finanzbildung.de/team/" target="_blank">Über uns</a>
            </li>
        </ul>
    </nav>
@endsection

@section('footer')
    <button
        type="button"
        @class([
           "button",
           "button--type-secondary",
        ])
        wire:click="toggleSidebarMenu()"
    >
        Schließen
    </button>
@endsection
