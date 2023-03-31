export default () => ({
    init() {
        //@ts-ignore
        const toTopButton = this.$refs.toTopButton;

        const body = document.body
        const html = document.documentElement

        // Get the highest of these values because the heights could differ (with vs. without margins, etc)
        // To get the document height
        const documentHeight = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );

        // We don't have to show the to-top-button, when we can see all of the content at once
        if(documentHeight <= window.innerHeight) {
            const buttonWrapper = toTopButton.closest('.to-top-button__wrapper')
            buttonWrapper.classList.add('is-hidden')
        }

        toTopButton.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    },
});
