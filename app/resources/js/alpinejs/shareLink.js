export default () => ({
    async shareLink(title, url) {
        try {
            await navigator.share({
                title: title,
                url: url
            });
        } catch (err) {
            if (err.name !== 'AbortError') {
                await navigator.clipboard.writeText(url);
                alert('Link wurde in die Zwischenablage kopiert.');
            }
        }
    },
})
