const BUTTON_TO_TOP_WRAPPER_CLASS = 'button-to-top__wrapper'

export default () => ({
    init() {
        //@ts-ignore
        const buttonToTop = this.$refs.buttonToTop;

        const body = document.body
        const html = document.documentElement

        // Get the highest of these values because the heights could differ (with vs. without margins, etc)
        // To get the document height
        const documentHeight = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );

        // We don't have to show the button-to-top, when we can see all of the content at once
        if(documentHeight <= window.innerHeight) {
            const buttonWrapper = buttonToTop.closest(`.${BUTTON_TO_TOP_WRAPPER_CLASS}`)
            buttonWrapper.classList.add(`${BUTTON_TO_TOP_WRAPPER_CLASS}--is-hidden`)
        }

        buttonToTop.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    },
});
