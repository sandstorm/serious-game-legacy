@props([
    'report',
])

@php
    /** @var \Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport $report */
@endphp

@if (count($report->highlights) === 0)
    <p>In dieser Partie ist nichts Besonderes passiert.</p>
@else
    <div class="game-report__highlights">
        @foreach ($report->highlights as $highlight)
            <article @class([
                'game-report__highlight',
                $highlight->playerId !== null ? $report->getPlayer($highlight->playerId)->colorClass : 'game-report__highlight--everyone',
            ])>
                <h4 class="game-report__highlight-title">{{ $highlight->title }}</h4>
                <p>{{ $highlight->text }}</p>
            </article>
        @endforeach
    </div>
@endif
