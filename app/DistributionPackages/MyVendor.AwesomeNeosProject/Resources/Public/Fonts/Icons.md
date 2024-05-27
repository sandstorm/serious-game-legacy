# We use IcoMoon to generate a icon font

## Adding new icons to the font

* Visit https://icomoon.io/app/#/projects *
* Log into our icomoon@sandstorm.de account (password is in bitwarden) and load the `neos kickstarter` project

## Adding changed icon font to Neos

* if you want to add new icons note that we used font awesome icons in the past
* export icons in icomoon and download the zip file
* move the new font files to `Public/Fonts`
* for cache busting: give the files a new version number in the file name (sth like `_v5`) and update the icon font file paths in `Private/tailwind.css` accordingly
* open the style.css from the zip file and copy the new icon classes to `Private/Styles/icons.css`
