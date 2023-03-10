## We use IcoMoon to generate a icon font.

### Adding new icons to the font

* Visit https://icomoon.io/app/#/projects *
* import the `selection.json` from this Folder to make changes to the font.

### Adding changed icon font to neos

* export icons in icomoon and download the zip file
* move the new font files to `Public/Fonts`
* override the `Public/Fonts/selection.json` with the new one
* open the style.css from the zip file and copy the new icon classes to `Private/GeneralStyles/_Icons.scss`

