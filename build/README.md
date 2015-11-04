## Building the data

The scripts in this `build/` sub directory allow you to rebuild the data files 
from a mix of sources, including unicode.org standards and the gemoji project.

You can rebuild by following these steps:

	cd build/

	# Rebuild catalog.php from the original data tables
	wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
	patch < source_html.patch
	php build_catalog.php full.html > catalog.php

	# Rebuild the master mapping
	wget http://www.unicode.org/Public/UNIDATA/UnicodeData.txt
	php build_map.php

	# From the final mapping, build a preview table
	php build_table.php > ../table.htm

	# Rebuild positions and make the master spritesheets
	# (This step requires ImageMagick or GraphicsMagick)
	php build_image.php # this is slow!
	php build_sheets.php
	optipng -o7 ../sheet_*.png # this is _very_ slow

To find out how to extract the original source glyphs, look inside the sub
directories under `build/`.
