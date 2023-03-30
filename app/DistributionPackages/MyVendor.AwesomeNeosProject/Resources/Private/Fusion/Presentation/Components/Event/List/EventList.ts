const CLASS_NAMES = {
    tag: '.event-list__tag',
}
const TAGS_QUERY_PARAM = 'tags'

export default () => ({
    filterOpen: false,

    // method calculates the correct new 'tags' query parameter from the checked state of the clicked element
    // and the current tags -> sets the new query parameter and reloads the page
    filterByTagClick(event: PointerEvent) {
        const element = event.currentTarget as HTMLInputElement
        const tagName = element.id.toLowerCase()

        const urlParams = new URLSearchParams(window.location.search)
        const tagsQueryParam = urlParams.get(TAGS_QUERY_PARAM)?.toLowerCase()
        const tagsFromUrlArray = tagsQueryParam?.split(',') || []

        let newTagsParam: string = ''
        if (element.checked) {
            // element is set from inactive to active
            newTagsParam = tagsQueryParam ? `${tagsQueryParam},${tagName}` : tagName
        } else {
            // element is set from active to inactive
            const index = tagsFromUrlArray.indexOf(tagName)
            if (index > -1) {
                tagsFromUrlArray.splice(index, 1)
            }
            newTagsParam = tagsFromUrlArray.join(',')
        }

        newTagsParam ? urlParams.set(TAGS_QUERY_PARAM, newTagsParam) : urlParams.delete(TAGS_QUERY_PARAM)
        const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + urlParams.toString()
        window.history.pushState({newUrl}, '', newUrl)

        // for now we just reload the page, the actual filtering is done by fusion
        location.reload()
    },

    // deletes the 'tags' query parameter and reloads the page
    deleteFilter() {
        const urlParams = new URLSearchParams(window.location.search)
        urlParams.delete(TAGS_QUERY_PARAM)
        const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + urlParams.toString()
        window.history.pushState({newUrl}, '', newUrl)


        // for now we just reload the page, the actual filtering is done by fusion
        location.reload()
    }
})
