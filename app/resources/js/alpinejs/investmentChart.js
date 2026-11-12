/**
 * Details of the trade markers in an investment chart of the game report.
 * A mouse shows the details while hovering, touch and keyboard select a marker until it is tapped again.
 */
export default () => ({
    hovered: null,
    selected: null,

    get current() {
        return this.hovered ?? this.selected
    },

    enter(event, index) {
        if (event.pointerType === 'mouse') {
            this.hovered = index
        }
    },

    leave() {
        this.hovered = null
    },

    toggle(index) {
        this.selected = this.selected === index ? null : index
    },

    close() {
        this.selected = null
    },
})
