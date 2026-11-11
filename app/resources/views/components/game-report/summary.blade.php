@props([
    'report',
    'myself',
])

@php
    /** @var \Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport $report */
    $winner = $report->getWinner();
    $numberOfYears = count($report->timeline->yearStarts);
    $numberOfSpielzuege = max(0, $report->timeline->getNumberOfValues() - 1);
@endphp

<section class="game-report__summary">
    <div class="game-report__headline">
        @if ($winner !== null)
            <h1 class="game-report__title">{{ $winner->name }} hat das Lebensziel erreicht!</h1>
            <p class="game-report__lede">„{{ $winner->lebenszielName }}“ ist geschafft. Damit endet die Partie nach {{ $numberOfYears }} {{ $numberOfYears === 1 ? 'Jahr' : 'Jahren' }} und {{ $numberOfSpielzuege }} Spielzügen.</p>
        @else
            <h1 class="game-report__title">Spielbericht</h1>
            <p class="game-report__lede">{{ $numberOfYears }} {{ $numberOfYears === 1 ? 'Jahr' : 'Jahre' }}, {{ $numberOfSpielzuege }} Spielzüge.</p>
        @endif
    </div>

    <div class="game-report__players">
        @foreach ($report->players as $player)
            <article @class([
                'game-report__player',
                $player->colorClass,
                'game-report__player--winner' => $player->hasFinishedLebensziel,
            ])>
                <header class="game-report__player-header">
                    <h3 class="game-report__player-name">
                        {{ $player->name }}
                        @if ($player->playerId->equals($myself))
                            <span class="game-report__you">(du)</span>
                        @endif
                    </h3>
                    @if ($player->hasFinishedLebensziel)
                        <span class="game-report__badge"><i class="icon-fertig" aria-hidden="true"></i> Lebensziel erreicht</span>
                    @endif
                </header>

                <p class="game-report__lebensziel">{{ $player->lebenszielName }}</p>

                <div class="game-report__phases" aria-label="Lebenszielphase {{ $player->reachedPhase->value }} von 3">
                    @foreach ([1, 2, 3] as $phase)
                        <span @class([
                            'game-report__phase',
                            'game-report__phase--reached' => $phase <= $player->reachedPhase->value,
                            'game-report__phase--finished' => $phase === 3 && $player->hasFinishedLebensziel,
                        ])>
                            Phase {{ $phase }}
                            @if (isset($player->phaseReachedInYear[$phase]))
                                <small>Jahr {{ $player->phaseReachedInYear[$phase] }}</small>
                            @endif
                        </span>
                    @endforeach
                </div>

                <dl class="game-report__figures">
                    <dt>Guthaben am Ende</dt>
                    <dd><x-money-amount :value="$player->finalGuthaben" /></dd>
                    @if ($player->assetsValue !== null && $player->assetsValue->value != 0)
                        <dt>Anlagen und Immobilien</dt>
                        <dd><x-money-amount :value="$player->assetsValue" /></dd>
                    @endif
                    @if ($player->openLoanRepayments->value != 0)
                        <dt>Offene Kredite</dt>
                        <dd><x-money-amount :value="$player->openLoanRepayments->negate()" /></dd>
                    @endif
                    <dt class="game-report__figures-total">Vermögen</dt>
                    <dd class="game-report__figures-total">
                        @if ($player->getNetWorth() !== null)
                            <x-money-amount :value="$player->getNetWorth()" />
                        @else
                            nicht berechenbar
                        @endif
                    </dd>
                </dl>
            </article>
        @endforeach
    </div>
</section>
