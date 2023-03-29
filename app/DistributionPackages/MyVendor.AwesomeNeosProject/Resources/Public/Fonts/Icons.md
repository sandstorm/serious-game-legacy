## We use IcoMoon to generate an icon font.

### Adding new icons to the font

* Visit https://icomoon.io/app/#/projects *
* select "Import Project" and load the  `selection.json` from this folder
* after importing click on "Load" and select the library you want to import icons from (we used "Font Awesome" icons in the past)
* select the icon(s) you want to add to our selection

### Adding changed icon font to neos

* select "Generate Font" at the bottom of the screen and then "Download" to downliad the `.zip` File
* move the new font files to `Public/Fonts` (`icons.svg`, `icons.ttf`, `icons.woff`)
* overwrite the `Public/Fonts/selection.json` with the new one
* open the `style.css from the zip file and copy the new icon classes to `Private/GeneralStyles/_Icons.scss`

