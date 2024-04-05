const plugin = require('tailwindcss/plugin')

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./**/*.{fusion,ts,html}'],
    theme: {
        fontFamily: {
            sans: ['Arial', 'Helvetica', 'sans-serif'],
        },
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
                html: {
                    fontSize: '20px',
                    lineHeight: 1.4,
                    fontWeight: 400
                },
                body: {
                    fontSize: '20px',
                    lineHeight: 1.4,
                    fontWeight: 400
                },
                h1: {
                    fontSize: '3.2rem',
                    lineHeight: 1.2
                },
                h2: {
                    fontSize: '2.9rem',
                    lineHeight: 1.2
                },
                h3: {
                    fontSize: '1.6rem',
                    lineHeight: 1.2
                },
                h4: {
                    fontSize: '1.2rem',
                    lineHeight: 1.2
                },
                h5: {
                    fontSize: '1.1rem',
                    lineHeight: 1.2
                },
                h6: {
                    fontSize: '1rem',
                    lineHeight: 1.2
                },
            })
        }),
    ],
}
