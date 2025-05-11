@use(Domain\CoreGameLogic\Dto\ValueObject\GameId)
<x-layout>
    <h2>Neues Spiel</h2>
    <form method="get" action={{ route('game-play.player-links', ['gameId' => GameId::random()->value]) }}>
        Anzahl Spieler: <x-form.textfield name="numberOfPlayers" />
        <x-form.submit>Spiel erstellen</x-form.submit>
    </form>
</x-layout>
