@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState')

@props(['konjunkturphase' => null, 'currentPage' => 1])

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div class="konjunkturphase-start">
    <div class="konjunkturphase-start__content">
        @if ($currentPage === 0)
            <div class="konjunkturphase-start__info">
                <h1>Eine neue Konjunkturphase beginnt.</h1>
                <h2>Das nächste Szenario ist:</h2>
                <h2><strong>{{$konjunkturphase->type->value}}</strong></h2>
            </div>
        @elseif ($currentPage === 1)
            <h2><strong>{{$konjunkturphase->type->value}}</strong></h2>
            <p class="font-size--xl">
                {{ $konjunkturphase->description }}
            </p>
        @endif
    </div>

    <footer class="konjunkturphase-start__actions">
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
    </footer>
</div>
