# Building the Apple emoji images

Get a copy of the TTC file from a mac - `/System/Library/Fonts/Apple Color Emoji.ttc`.

You may need to install a Perl module first:

    perl -MCPAN -e"install Font::TTF"

Then run the extractor script:

    perl extract.pl

This will populate the latest image files into `../../img-apple-160`.

Next you'll want to cut the 64px versions that are used in the sheets"

    php make64.php


## Updating to new Unicode versions

When updating the image-set to add new codepoints, there's a confusing sequence required:

* Update the unicode data files
* Run `build_map.php` to update what codepoints are in the main JSON catalog
* Run `apple/extract.pl` to pull the 160px images
* Run `apple/make64.php` to make the 64px images
* Re-run `build_map.php` to pick up which images apple supports
* Run `build_image.php` to build the spritesheet


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
