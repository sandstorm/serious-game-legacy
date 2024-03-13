import { AlpineComponent } from 'alpinejs'

const TAGS_QUERY_PARAM = 'tags'

export type EventListComponent = {
    filterOpen: boolean

    toggleFilter: () => void
    closeFilter: () => void
    filterByTagClick: (event: PointerEvent) => void
    deleteFilter: () => void
    setNewUrl: (urlParams: URLSearchParams) => void
    sanitizeQueryParameter: (text: string) => string
}

export default (): AlpineComponent<EventListComponent> => ({
    filterOpen: false,

    toggleFilter() {
        this.filterOpen = !this.filterOpen
    },

    closeFilter() {
        this.filterOpen = false
    },

    // method calculates the correct new 'tags' query parameter from the checked state of the clicked element
    // and the current tags -> sets the new query parameter and reloads the page
    filterByTagClick(event) {
        const element = event.currentTarget as HTMLInputElement
        const tagName = this.sanitizeQueryParameter(element.id)

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

        this.setNewUrl(urlParams)
    },

    // deletes the 'tags' query parameter and reloads the page
    deleteFilter() {
        const urlParams = new URLSearchParams(window.location.search)
        urlParams.delete(TAGS_QUERY_PARAM)

        this.setNewUrl(urlParams)
    },

    setNewUrl(urlParams) {
        const newUrl =
            urlParams.toString() !== ''
                ? `${window.location.protocol}//${window.location.host}${
                      window.location.pathname
                  }?${urlParams.toString()}`
                : `${window.location.protocol}//${window.location.host}${window.location.pathname}`

        window.history.pushState({ newUrl }, '', newUrl)

        // for now we just reload the page, the actual filtering is done by fusion
        location.reload()
    },

    // the analogue function exists in FilterNodesByReferenceHelper.php -> keep in sync
    sanitizeQueryParameter(text: string): string {
        text = text.toLowerCase()

        const regexReplacements: { [key: string]: string } = {
            'ä': 'ae',
            'ö': 'oe',
            'ü': 'ue',
            'ß': 'ss',
            '%': '-',
            'î': 'i',
            'ç': 'c',
            '°': 'o',
            '@': 'at',
            '[áàâ]': 'a',
            '[éèê]': 'e',
            '[óòô]': 'o',
            '[úùû]': 'u',
            '[\\(\\)]': '',
            '[\"<>]': '',
            '[+,:\'\\s\\/#!?&.*]+': '-',
            '-+': '-',
            '(^-)|(-$)': '',
            '[^a-z0-9._~-]': '_',
        }

        for (let pattern in regexReplacements) {
            const regExp = new RegExp(pattern, 'gu')
            text = text.replace(regExp, regexReplacements[pattern])
        }

        // remove duplicates
        const noDuplicates = ['-', '_']
        noDuplicates.forEach((char) => {
            const regExp = new RegExp(char + char + '+', 'g')
            text = text.replace(regExp, char)
        })

        return text
    },
})
