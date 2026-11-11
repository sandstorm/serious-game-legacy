@props([
    'report',
])

@php
    use Domain\CoreGameLogic\Feature\GameReport\Dto\KeyMomentType;

    /** @var \Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport $report */
    $icons = [
        KeyMomentType::LEBENSZIELPHASE_CHANGED->value => 'icon-phasenwechsel',
        KeyMomentType::LEBENSZIEL_FINISHED->value => 'icon-fertig',
        KeyMomentType::EREIGNIS->value => 'icon-ereignis',
        KeyMomentType::INSOLVENZ->value => 'icon-insolvent',
        KeyMomentType::LOAN_TAKEN->value => 'icon-euro',
        KeyMomentType::LOAN_REPAID->value => 'icon-euro',
        KeyMomentType::IMMOBILIE_BOUGHT->value => 'icon-immobilien',
        KeyMomentType::IMMOBILIE_SOLD->value => 'icon-immobilien',
        KeyMomentType::BIG_INVESTMENT_TRADE->value => 'icon-aktien',
    ];
@endphp

<ol class="game-report__years">
    @foreach ($report->timeline->yearStarts as $yearStart)
        @php $moments = $report->getKeyMomentsByYear()[$yearStart->year->value] ?? []; @endphp
        <li class="game-report__year game-report__year--{{ strtolower($yearStart->type->name) }}">
            <div class="game-report__year-label">
                <strong>Jahr {{ $yearStart->year->value }}</strong>
                {{-- the names of the Konjunkturphasen usually start with their type, e.g. "Aufschwung IV – Späte Phase" --}}
                <small>{{ str_starts_with($yearStart->name, $yearStart->type->value) ? $yearStart->name : $yearStart->type->value . ' · ' . $yearStart->name }}</small>
            </div>
            @if (count($moments) === 0)
                <p class="game-report__year-quiet">Ein ruhiges Jahr.</p>
            @else
                <ul class="game-report__moments">
                    @foreach ($moments as $moment)
                        @php $player = $report->getPlayer($moment->playerId); @endphp
                        <li class="game-report__moment {{ $player->colorClass }}">
                            <i class="{{ $icons[$moment->type->value] }}" aria-hidden="true"></i>
                            <div class="game-report__moment-text">
                                <span><strong>{{ $player->name }}</strong> {{ $moment->text }}</span>
                                @if ($moment->detail !== null)
                                    <small>{{ $moment->detail }}</small>
                                @endif
                            </div>
                            @if ($moment->guthabenChange !== null)
                                <span class="game-report__moment-amount"><x-money-amount :value="$moment->guthabenChange" :with-sign="true" /></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ol>
