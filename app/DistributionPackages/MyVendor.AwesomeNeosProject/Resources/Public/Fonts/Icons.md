## We use IcoMoon to generate a icon font.

### Adding new icons to the font

* Visit https://icomoon.io/app/#/projects *
* import the `selection.json` from this Folder to make changes to the font.

### Adding changed icon font to neos

* if you want to add new icons note that we used font awesome icons in the past
* export icons in icomoon and download the zip file
* move the new font files to `Public/Fonts`
* for cache busting: give the files a new version number in the file name (sth like `_v5`) and update the icon font file
  paths in `Private/GeneralStyles/_Typography.scss` accordingly
* override the `Public/Fonts/selection.json` with the new one
* open the style.css from the zip file and copy the new icon classes to `Private/GeneralStyles/_Icons.scss`
