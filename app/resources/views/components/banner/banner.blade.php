@use('\Domain\Definitions\Card\Dto\ResourceChanges')

@if ($this->isBannerVisible)
    <div
        x-data="{ visible: @entangle('isBannerVisible'), message: @entangle('bannerMessage'), timer: null }"
        @class([
            "banner",
            $this->getPlayerColorClass()
        ])
        x-init="
            timer = setTimeout(() => $wire.closeBanner(), 10000);
            $watch('message', () => {
                // reset css animation on button
                $refs.bannerCloseButton.style.animation = 'none';
                $refs.bannerCloseButton.offsetHeight;
                $refs.bannerCloseButton.style.animation = null;
                // reset timer
                clearTimeout(timer);
                // set new timer
                timer = setTimeout(() => $wire.closeBanner(), 10000)
            })"
    >
        <div class="banner__content"
            @if ($this->getBannerResourceChanges())
                <div class="banner__resource-changes">
                    <x-gameboard.resourceChanges.resource-changes style-class="horizontal" :resource-changes="$this->getBannerResourceChanges()" />
                </div>
            @endif

            <div class="banner__body">
                {{$this->bannerMessage}}
            </div>

            <button x-ref="bannerCloseButton" class="banner__close button button--type-text" wire:click="closeBanner()">
                <i class="icon-close" aria-hidden="true"></i>
                <span class="sr-only">Banner schließen</span>
            </button>
        </div>
    </div>
@endif
