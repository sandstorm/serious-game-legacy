@use('\Domain\Definitions\Card\Dto\ResourceChanges')

<div
    x-data="{ visible: false, message: @entangle('bannerMessage'), timer: null }"
    @class([
        "banner",
        $this->getPlayerColorClass()
    ])
    x-cloak
    x-show="visible"
    x-init="
        timer = setTimeout(() => {visible = false; message = null}, 10000);
        $watch('message', () => {
            if (!message) {
                return;
            }
            visible = true;
            // reset css animation on button
            $refs.bannerCloseButton.style.animation = 'none';
            $refs.bannerCloseButton.offsetHeight;
            $refs.bannerCloseButton.style.animation = null;
            // reset timer
            clearTimeout(timer);
            // set new timer
            timer = setTimeout(() => {visible = false; message = null}, 10000)
        })"
    role="status"
>
    <div class="banner__content"
        @if ($this->getBannerResourceChanges())
            <div class="banner__resource-changes">
                <x-gameboard.resourceChanges.resource-changes style-class="horizontal" :resource-changes="$this->getBannerResourceChanges()" />
            </div>
        @endif

        <div class="banner__body">
            <span x-text="message"></span>
        </div>

        <button x-ref="bannerCloseButton" class="banner__close button button--type-text" x-on:click="clearTimeout(timer); visible = false; message = null">
            <i class="icon-close" aria-hidden="true"></i>
            <span class="sr-only">Banner schließen</span>
        </button>
    </div>
</div>
