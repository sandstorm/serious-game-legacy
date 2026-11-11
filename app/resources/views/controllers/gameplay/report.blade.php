@props([
    'report',
    'myself',
])

<x-layout>
    <x-slot:title>Spielbericht</x-slot:title>
    <x-header.header title="Spielbericht" />

    <div class="game-report">
        <x-game-report.summary :report="$report" :myself="$myself" />

        @if ($report->isFromOlderDefinitionsVersion)
            <p class="game-report__notice">
                <i class="icon-info" aria-hidden="true"></i>
                Dieses Spiel wurde mit einer älteren Version des Spiels erstellt. Die Jahresabrechnungen werden deshalb
                nur als Summe gezeigt, und Karten, die es nicht mehr gibt, erscheinen mit ihrer Kennung.
            </p>
        @endif

        <section class="game-report__section">
            <h2>Was diese Partie besonders gemacht hat</h2>
            <x-game-report.highlights :report="$report" />
        </section>

        <section class="game-report__section">
            <h2>Guthaben im Spielverlauf</h2>
            <p class="game-report__intro">Das Guthaben aller Spieler:innen nach jedem Spielzug. Die farbigen Bänder zeigen die Konjunkturphasen.</p>
            <x-game-report.guthaben-chart :report="$report" />
        </section>

        <section class="game-report__section">
            <h2>Wohin das Geld geflossen ist</h2>
            <p class="game-report__intro">Alle Einnahmen und Ausgaben über das ganze Spiel. Jede Spalte ergibt zusammen das Guthaben am Ende.</p>
            <x-game-report.money-table :report="$report" />
        </section>

        <section class="game-report__section">
            <h2>Schlüsselmomente</h2>
            <x-game-report.key-moments :report="$report" />
        </section>

        <section class="game-report__section">
            <h2>Karrieren</h2>
            <x-game-report.careers :report="$report" />
        </section>

        <div class="game-report__actions">
            <a class="button button--type-primary" href="{{ route('game-play.index') }}">Zur Spielübersicht</a>
        </div>
    </div>
</x-layout>
