# Building the Apple emoji images

Get a copy of the TTF file from a mac - `/System/Library/Fonts/Apple Color Emoji.ttf`.

Then run the extractor script:

    perl extract.pl

This will populate the latest image files into `../../img-apple-160`.

Next you'll want to cut the 64px versions that are used in the sheets"

    php make64.php

And finally optimize them. This requires OptiPNG to be installed (http://optipng.sourceforge.net/)
and takes a very long time:

    optipng -o7 ../../img-apple-64/*

The resulting 64px images are then ready to use.


## Catalog

On a Mac running OS X you can also extract the latest version of the emoji categorization:

    ./extract_categories.sh

You can then use this data to check for new ligatures:

    php categories.php


## Apple's TTF Format

The breakdown of tables in the Apple font (ordered by size) are as follows:

| Table |  Size    | Description |
|:----- | --------:| --- |
| sbix  | 57824724 | ... |
| glyf  |   189996 | ... |
| morx  |    29772 | ... |
| post  |    14856 | ... |
| loca  |     5632 | ... |
| hmtx  |     2904 | ... |
| vmtx  |     2822 | ... |
| name  |     2028 | ... |
| cmap  |     1960 | ... |
| meta  |      540 | ... |
| OS/2  |       96 | ... |
| trak  |       60 | ... |
| head  |       54 | ... |
| hhea  |       36 | ... |
| vhea  |       36 | ... |
| maxp  |       32 | ... |

The actual images are stored in the sbix table: https://developer.apple.com/fonts/TrueType-Reference-Manual/RM06/Chap6sbix.html

For simple characters, the mapping of character to glyph ID can be found in the cmap table: https://developer.apple.com/fonts/TrueType-Reference-Manual/RM06/Chap6cmap.html

For compound characters, the mapping can be found in the ligatures feature of the morx table: https://developer.apple.com/fonts/TrueType-Reference-Manual/RM06/Chap6morx.html
