import TouchEvent from '@/TouchEvent'

// provides slide up to close the player list
export default () => ({
    touchEvent: null,
    playerListOpen: false,

    touchStart(event) {
        this.touchEvent = new TouchEvent(event)
    },

    touchEnd() {
        if (!this.touchEvent) {
            return;
        }

        this.touchEvent.setEndEvent(event);

        if (this.touchEvent.isSwipeUp()) {
            this.playerListOpen = false
        }

        // Reset event for next touch
        this.touchEvent = null;
    }
})
