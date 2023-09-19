const BUTTON_TO_TOP_WRAPPER_CLASS = "button-to-top__wrapper";

export default () => ({
    previousScrollPosition: 0,
    init() {
        //@ts-ignore
        const buttonToTop = this.$refs.buttonToTop;

        buttonToTop.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });

        window.addEventListener("scroll", () => this.handleScroll());
    },
    isScrollingDown() {
        let goingDown = false;

        let scrollPosition = window.pageYOffset;

        if (scrollPosition > this.previousScrollPosition) {
            goingDown = true;
        }

        this.previousScrollPosition = scrollPosition;

        return goingDown;
    },
    handleScroll() {
        //@ts-ignore
        const buttonToTop = this.$refs.buttonToTop;

        const buttonWrapper = buttonToTop.closest(
            `.${BUTTON_TO_TOP_WRAPPER_CLASS}`
        );

        // We just want to show the button-to-top when the user indicates to scroll up and when we are not already at the top of the page
        // Otherwise we hide the button
        if (window.pageYOffset === 0 || this.isScrollingDown()) {
            if (
                !buttonWrapper.classList.contains(
                    `${BUTTON_TO_TOP_WRAPPER_CLASS}--is-hidden`
                )
            ) {
                buttonWrapper.classList.add(
                    `${BUTTON_TO_TOP_WRAPPER_CLASS}--is-hidden`
                );
            }
        } else if (
            !this.isScrollingDown() &&
            window.pageYOffset > window.innerHeight
        ) {
            buttonWrapper.classList.remove(
                `${BUTTON_TO_TOP_WRAPPER_CLASS}--is-hidden`
            );
        }
    },
});
