/**
 * Tooltip for the Guthaben chart of the game report, shown while hovering or after tapping the chart.
 * The value at index i is drawn at x = plotLeft + (i + 1) / count * (plotRight - plotLeft).
 */
export default (data, plotLeft, plotRight) => ({
    data,
    index: null,
    crossX: 0,
    tooltipLeft: 0,

    move(event) {
        const svg = this.$refs.svg
        const point = svg.createSVGPoint()
        point.x = event.clientX
        point.y = event.clientY
        const location = point.matrixTransform(svg.getScreenCTM().inverse())
        const position = (location.x - plotLeft) / (plotRight - plotLeft) * data.count
        const numberOfValues = data.players.length > 0 ? data.players[0].values.length : 0
        if (numberOfValues === 0) {
            return
        }
        this.index = Math.max(0, Math.min(numberOfValues - 1, Math.round(position) - 1))
        this.crossX = plotLeft + (this.index + 1) / data.count * (plotRight - plotLeft)

        const bounds = this.$el.getBoundingClientRect()
        const left = event.clientX - bounds.left + 16
        this.tooltipLeft = left + 220 > bounds.width ? left - 252 : left
    },

    /**
     * A touch ends with leaving the chart, so the tooltip of a tap stays open until it is closed.
     */
    leave(event) {
        if (event.pointerType !== 'touch') {
            this.index = null
        }
    },

    close() {
        this.index = null
    },

    rows() {
        if (this.index === null) {
            return []
        }
        const formatter = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
        return data.players
            .map(player => ({
                name: player.name,
                colorClass: player.colorClass,
                value: player.values[this.index],
                formatted: formatter.format(player.values[this.index]),
            }))
            .sort((a, b) => b.value - a.value)
    },
})
