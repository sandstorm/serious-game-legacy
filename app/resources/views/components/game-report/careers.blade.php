@props([
    'report',
])

@php
    /** @var \Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport $report */
@endphp

<div class="game-report__careers">
    @foreach ($report->players as $player)
        <div class="game-report__career {{ $player->colorClass }}">
            <h4 class="game-report__career-name"><span class="game-report__dot"></span>{{ $player->name }}</h4>
            @if (count($player->career) === 0)
                <p>Kein Job angenommen, {{ $player->numberOfMinijobs }} {{ $player->numberOfMinijobs === 1 ? 'Minijob' : 'Minijobs' }}.</p>
            @else
                <ol class="game-report__career-steps">
                    @foreach ($player->career as $step)
                        <li @class(['game-report__career-step', 'game-report__career-step--quit' => $step->isQuit()])>
                            <span>{{ $step->title }}</span>
                            <small>
                                Jahr {{ $step->year->value }}
                                @if ($step->gehalt !== null)
                                    · <x-money-amount :value="$step->gehalt" />
                                @endif
                            </small>
                        </li>
                    @endforeach
                </ol>
                <p class="game-report__career-minijobs">Dazu {{ $player->numberOfMinijobs }} {{ $player->numberOfMinijobs === 1 ? 'Minijob' : 'Minijobs' }}.</p>
            @endif
        </div>
    @endforeach
</div>
