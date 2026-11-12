<div class="guthaben-chart"
     x-data="guthabenChart(@js($hoverData), {{ $plotLeft }}, {{ $plotRight }})"
     @click.outside="close()"
     @keydown.escape.window="close()"
>
    <div class="guthaben-chart__legend">
        @foreach ($report->players as $player)
            <span class="guthaben-chart__legend-item {{ $player->colorClass }}">{{ $player->name }}</span>
        @endforeach
    </div>

    <div class="guthaben-chart__scroll">
        <svg class="guthaben-chart__svg"
             viewBox="0 0 {{ $width }} {{ $height }}"
             role="img"
             aria-label="Guthaben aller Spieler:innen nach jedem Spielzug"
             x-ref="svg"
             @pointerdown="move($event)"
             @pointermove="move($event)"
             @pointerleave="leave($event)"
        >
            @foreach ($bands as $band)
                <rect class="guthaben-chart__band guthaben-chart__band--{{ $band['modifier'] }}"
                      x="{{ $band['x'] }}" y="{{ $plotTop }}" width="{{ $band['width'] }}" height="{{ $plotBottom - $plotTop }}">
                    <title>{{ $band['title'] }}</title>
                </rect>
                <text class="guthaben-chart__axis-label" x="{{ $band['labelX'] }}" y="{{ $plotBottom + 20 }}" text-anchor="middle">{{ $band['label'] }}</text>
            @endforeach

            @foreach ($ticks as $tick)
                <line class="guthaben-chart__grid" x1="{{ $plotLeft }}" x2="{{ $plotRight }}" y1="{{ $tick['y'] }}" y2="{{ $tick['y'] }}" />
                <text class="guthaben-chart__axis-label" x="{{ $plotLeft - 8 }}" y="{{ $tick['y'] + 4 }}" text-anchor="end">{{ $tick['label'] }}</text>
            @endforeach

            @foreach ($lines as $line)
                <g class="{{ $line['colorClass'] }}">
                    <polyline class="guthaben-chart__line" points="{{ $line['points'] }}" />
                    <circle class="guthaben-chart__end" cx="{{ $line['endX'] }}" cy="{{ $line['endY'] }}" r="4" />
                    <text class="guthaben-chart__line-label" x="{{ $line['endX'] + 10 }}" y="{{ $line['labelY'] + 4 }}">{{ $line['name'] }}</text>
                </g>
            @endforeach

            <line class="guthaben-chart__crosshair" x-show="index !== null" x-cloak
                  :x1="crossX" :x2="crossX" y1="{{ $plotTop }}" y2="{{ $plotBottom }}" />
        </svg>
    </div>

    <div class="guthaben-chart__tooltip" x-show="index !== null" x-cloak :style="`left: ${tooltipLeft}px`">
        <strong x-text="data.years[index] ?? ''"></strong>
        <span x-text="index === data.count - 1 ? 'Spielende' : 'nach Spielzug ' + (index + 1)"></span>
        <template x-for="row in rows()" :key="row.name">
            <div class="guthaben-chart__tooltip-row" :class="row.colorClass">
                <span x-text="row.name"></span>
                <span x-text="row.formatted"></span>
            </div>
        </template>
    </div>
</div>
