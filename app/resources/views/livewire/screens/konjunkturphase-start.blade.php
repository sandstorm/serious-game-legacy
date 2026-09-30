@props(['konjunkturphase' => null, 'previousKonjunkturphase' => null, 'currentPage' => 0])

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="konjunkturphase-start">
    <div class="konjunkturphase-start__actions">
        <div></div>

        @if ($currentPage >= 1)
            <button wire:click="startKonjunkturphaseForPlayer()"
                    type="button"
                    class="button button--type-borderless">
                Weiter
            </button>
        @else
            <button wire:click="nextKonjunkturphaseStartScreenPage()"
                    type="button"
                    class="button button--type-borderless">
                Weiter
            </button>
        @endif
    </div>

    <div class="konjunkturphase-start__content">
        @if ($currentPage === 0)
            <div class="konjunkturphase-start__info">
                <h1>Eine neue Konjunkturphase beginnt.</h1>
                <h2>Das nächste Szenario ist:</h2>
                <h2><strong>{{$konjunkturphase->type->value}}</strong></h2>
                <img
                    class="konjunkturphase-start__image"
                    src="/images/{{ strtolower($konjunkturphase->type->value) }}.svg"
                    alt="{{ $konjunkturphase->type->value }}"
                />
            </div>
        @elseif ($currentPage === 1)
            <div class="konjunkturphase-start__info">
                <h2><strong>{{$konjunkturphase->type->value}}</strong> - Auswirkungen</h2>

                @php
                    $hasPrevious = $previousKonjunkturphase !== null;
                @endphp

                <table class="konjunkturphase-comparison">
                    <thead>
                        <tr>
                            <th>Kategorie</th>
                            @if ($hasPrevious)
                                <th>Vorher</th>
                            @endif
                            <th>Aktuell</th>
                            @if ($hasPrevious)
                                <th>Tendenz</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $previousAuswirkungen = $previousKonjunkturphase?->getDisplayedAuswirkungen() ?? [];
                        @endphp
                        @foreach ($konjunkturphase->getDisplayedAuswirkungen() as $index => $auswirkung)
                            @php
                                $currentValue = $auswirkung->value;
                                $previousValue = $previousAuswirkungen[$index]->value ?? null;
                                $suffix = $auswirkung->unit;
                                $lowerIsBetter = $auswirkung->isLowerBetter;
                            @endphp
                            <tr>
                                <td>{{ $auswirkung->label }}</td>
                                @if ($hasPrevious)
                                    <td><x-formatted-number :value="$previousValue" :suffix="$suffix" /></td>
                                @endif
                                <td><strong><x-formatted-number :value="$currentValue" :suffix="$suffix" /></strong></td>
                                @if ($hasPrevious)
                                    <td>
                                        @if ($currentValue > $previousValue)
                                            <i @class(['icon-trend-up', $lowerIsBetter ? 'text--danger' : 'text--success']) aria-hidden="true"></i>
                                            <span class="sr-only">{{ $lowerIsBetter ? 'verschlechtert' : 'verbessert' }}</span>
                                        @elseif ($currentValue < $previousValue)
                                            <i @class(['icon-trend-down', $lowerIsBetter ? 'text--success' : 'text--danger']) aria-hidden="true"></i>
                                            <span class="sr-only">{{ $lowerIsBetter ? 'verbessert' : 'verschlechtert' }}</span>
                                        @else
                                            <i class="icon-trend-same" aria-hidden="true"></i>
                                            <span class="sr-only">unverändert</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if (count($konjunkturphase->getDisplayedAuswirkungDescriptions()) > 0)
                        <tbody>
                            <tr>
                                <th colspan="{{ $hasPrevious ? 4 : 2 }}">Weitere Auswirkungen</th>
                            </tr>
                            @foreach ($konjunkturphase->getDisplayedAuswirkungDescriptions() as $auswirkungDescription)
                                <tr>
                                    <td colspan="{{ $hasPrevious ? 4 : 2 }}">{{ $auswirkungDescription }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        @endif
    </div>
</div>
