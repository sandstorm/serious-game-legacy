@use('Domain\CoreGameLogic\Feature\Initialization\State\PreGameState')

{{-- !!! Livewire components MUST have a single root element !!! --}}
<div>
    @if(PreGameState::isInPreGamePhase($this->gameStream()))
        @include("livewire.screens.pregame")
    @else
        @include("livewire.screens.ingame")
    @endif
</div>

