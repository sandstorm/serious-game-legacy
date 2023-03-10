import {sassPlugin} from 'esbuild-sass-plugin';
import esbuild from 'esbuild';

/**
 * This file contains the JS/CSS bundler configuration, as executed on `npm run build`
 */
esbuild.build({
    // Generic Options (shared between build.js and build-watch.js)
    entryPoints: ['./main.ts'],
    target: ['esnext'],
    bundle: true,
    sourcemap: true,
    outfile: '../Public/bundle.js',
    external: ['*.woff', '*.woff2', '*.svg', '*.ttf', '/_maptiles/frontend/v1/map-main.js'],

    // NOTE: if you want to use Tailwind.css in this setup,
    // you need to add the following plugin entry, where "postCssPlugin" is imported from "esbuild-plugin-postcss2";
    // and additionally a tailwind.config.js.
    //
    // postCssPlugin.default({
    //    plugins: [autoprefixer, tailwindcss]
    // })
    plugins: [sassPlugin({
        loadPaths: ['./', './node_modules'],
    })],


    // Specific options for "npm run build"
    minify: true,
}).catch(() => process.exit(1))
