<div class="investment-charts">
    <div class="investment-charts__legend">
        @foreach ($report->players as $player)
            <span class="investment-charts__legend-item {{ $player->colorClass }}">{{ $player->name }}</span>
        @endforeach
        <span class="investment-charts__legend-symbol">▲ Kauf</span>
        <span class="investment-charts__legend-symbol">▼ Verkauf</span>
    </div>

    <div class="investment-charts__grid">
        @foreach ($charts as $chart)
            <figure class="investment-charts__chart" x-data="investmentChart" @click.outside="close()" @keydown.escape="close()">
                <figcaption class="investment-charts__caption">
                    <strong>{{ $chart['name'] }}</strong>
                    <span class="investment-charts__change {{ $chart['change'] >= 0 ? 'investment-charts__change--isPositive' : 'investment-charts__change--isNegative' }}">
                        {{ $chart['startPrice'] }} → {{ $chart['endPrice'] }}
                        ({{ $chart['change'] >= 0 ? '+' : '' }}{{ number_format($chart['change'], 1, ',', '.') }} %)
                    </span>
                </figcaption>

                <svg class="investment-charts__svg"
                     viewBox="0 0 {{ $width }} {{ $height }}"
                     role="img"
                     aria-label="Kursverlauf {{ $chart['name'] }}: von {{ $chart['startPrice'] }} auf {{ $chart['endPrice'] }}"
                >
                    @foreach ($chart['bands'] as $band)
                        <rect class="investment-charts__band investment-charts__band--{{ $band['modifier'] }}"
                              x="{{ $band['x'] }}" y="{{ $plotTop }}" width="{{ $band['width'] }}" height="{{ $plotBottom - $plotTop }}">
                            <title>{{ $band['title'] }}</title>
                        </rect>
                        @if ($band['label'] !== '')
                            <text class="investment-charts__axis-label" x="{{ $band['labelX'] }}" y="{{ $plotBottom + 18 }}" text-anchor="middle">{{ $band['label'] }}</text>
                        @endif
                    @endforeach

                    @foreach ($chart['ticks'] as $tick)
                        <line class="investment-charts__grid-line" x1="{{ $plotLeft }}" x2="{{ $plotRight }}" y1="{{ $tick['y'] }}" y2="{{ $tick['y'] }}" />
                        <text class="investment-charts__axis-label" x="{{ $plotLeft - 6 }}" y="{{ $tick['y'] + 4 }}" text-anchor="end">{{ $tick['label'] }}</text>
                    @endforeach

                    <path class="investment-charts__line" d="{{ $chart['path'] }}" />

                    @foreach ($chart['markers'] as $index => $marker)
                        <g class="investment-charts__marker {{ $marker['colorClass'] }}"
                           :class="{ 'investment-charts__marker--isActive': current === {{ $index }} }"
                           role="button"
                           tabindex="0"
                           aria-label="{{ $marker['text'] }}"
                           :aria-pressed="selected === {{ $index }}"
                           @pointerenter="enter($event, {{ $index }})"
                           @pointerleave="leave()"
                           @click="toggle({{ $index }})"
                           @keydown.enter.prevent="toggle({{ $index }})"
                           @keydown.space.prevent="toggle({{ $index }})"
                        >
                            <circle class="investment-charts__marker-hit-area" cx="{{ $marker['x'] }}" cy="{{ $marker['y'] }}" r="12" />
                            <polygon class="investment-charts__marker-symbol" points="{{ $marker['points'] }}" />
                        </g>
                    @endforeach
                </svg>

                @if ($chart['markers'] !== [])
                    <p class="investment-charts__detail" aria-live="polite">
                        <span x-show="current === null">Tippe auf ein Dreieck, um den Kauf oder Verkauf zu sehen.</span>
                        @foreach ($chart['markers'] as $index => $marker)
                            <span class="investment-charts__detail-text {{ $marker['colorClass'] }}" x-show="current === {{ $index }}" x-cloak>{{ $marker['text'] }}</span>
                        @endforeach
                    </p>

                    <details class="investment-charts__trades">
                        <summary>Käufe und Verkäufe ({{ count($chart['markers']) }})</summary>
                        <ul class="investment-charts__trade-list">
                            @foreach ($chart['markers'] as $marker)
                                <li class="investment-charts__trade {{ $marker['colorClass'] }}">
                                    <span aria-hidden="true">{{ $marker['isSale'] ? '▼' : '▲' }}</span>
                                    {{ $marker['text'] }}
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </figure>
        @endforeach
    </div>
</div>
