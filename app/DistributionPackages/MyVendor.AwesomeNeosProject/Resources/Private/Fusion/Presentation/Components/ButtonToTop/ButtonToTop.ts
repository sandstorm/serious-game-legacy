import { AlpineComponent } from 'alpinejs'

export type ButtonToTopComponent = {
    isHidden: boolean
    previousScrollPosition: number

    init: () => void
    isScrollingDown: () => boolean
    handleScroll: () => void
}

export default (): AlpineComponent<ButtonToTopComponent> => ({
    isHidden: true,
    previousScrollPosition: 0,

    init() {
        const buttonToTop = this.$refs.buttonToTop as HTMLButtonElement

        if(!buttonToTop) return

        buttonToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' })
        })
    },

    isScrollingDown() {
        const scrollPosition = window.scrollY

        if (scrollPosition > this.previousScrollPosition) {
            this.previousScrollPosition = scrollPosition

            return true
        }

        this.previousScrollPosition = scrollPosition

        return false
    },

    handleScroll() {
        const buttonToTop = this.$refs.buttonToTop as HTMLButtonElement

        if(!buttonToTop) return

        // We just want to show the button-to-top when the user indicates to scroll up and when we are not already at the top of the page
        // Otherwise we hide the button
        if (window.scrollY === 0 || this.isScrollingDown()) {
            this.isHidden = true
        } else if (!this.isScrollingDown() && window.scrollY > window.innerHeight) {
            this.isHidden = false
        }
    },
})
