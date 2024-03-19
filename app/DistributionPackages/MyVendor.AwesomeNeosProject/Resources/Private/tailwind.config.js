const plugin = require('tailwindcss/plugin')

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./**/*.{fusion,ts}'],
    theme: {
        extend: {
            colors: {
                primary: {
                    main: '#00aeef',
                    dark: '#0089bc',
                },
                white: '#fff',
                black: '#222',
                grey: {
                    main: '#434343',
                    light: '#868686',
                    lighter: '#dadada',
                    dark: '#282828',
                },
                error: '#d71a06',
            },
            boxShadow: {
                'blog-post': '0 5px 30px -10px rgba(71, 71, 71, 0.3)',
                'main-menu': '5px 5px 10px rgba(34, 34, 34, 0.5)',
            },
        },
    },
    plugins: [
        plugin(function ({ addBase }) {
            addBase({
                html: { fontSize: '20px' },
            })
        }),
    ],
}
