## Using the data

...


## Rebuilding the data

The scripts in the `build/` sub directory allow you to rebuild the data files 
from the unicode.org source materials and the images from the gemoji project.

You can rebuild by following these steps:

	cd build/

	# Rebuild catalog.php from the original data tables
	wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
	patch < source_html.patch
	php build_catalog.php full.html > catalog.php

	# Rebuild positions and naming catalogs, and make the master spritesheets
	# (This step requires ImageMagick or GraphicsMagick)
	php build_image.php # this is slow!
	php build_sheets.php
	php build_names.php

	# Finally, build a single mapping file
	php build_map.php
