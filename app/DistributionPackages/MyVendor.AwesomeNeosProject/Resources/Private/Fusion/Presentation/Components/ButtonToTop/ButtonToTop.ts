import { AlpineComponent } from 'alpinejs'

const CSS_CLASSES = {
    buttonWrapper: '.button-to-top__wrapper',
    buttonWrapperHidden: 'button-to-top__wrapper--is-hidden'
}

export type ButtonToTopComponent = {
    previousScrollPosition: number

    init: () => void
    isScrollingDown: () => boolean
    handleScroll: () => void
}

export default (): AlpineComponent<ButtonToTopComponent> => ({
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

        const buttonWrapper = buttonToTop.closest(CSS_CLASSES.buttonWrapper)

        if(!buttonWrapper) return

        // We just want to show the button-to-top when the user indicates to scroll up and when we are not already at the top of the page
        // Otherwise we hide the button
        if (window.scrollY === 0 || this.isScrollingDown()) {
            if (!buttonWrapper.classList.contains(CSS_CLASSES.buttonWrapperHidden)) {
                buttonWrapper.classList.add(CSS_CLASSES.buttonWrapperHidden)
            }
        } else if (!this.isScrollingDown() && window.scrollY > window.innerHeight) {
            buttonWrapper.classList.remove(CSS_CLASSES.buttonWrapperHidden)
        }
    },
})
