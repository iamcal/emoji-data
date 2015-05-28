This project provides easy-to-parse data about emoji, along with a spritesheet-style 
images for use on the web.

You can see a catalog of the emoji data here: http://unicodey.com/emoji-data/table.htm

Images are extracted from their sources and this library attempts to track the latest
available versions. If you're looking for older versions of Apple of Android images
(such as the Hairy Heart) then you'll need to look at previous revisions.

* Apple Emoji: Copyright &copy; Apple Inc. - OS X 10.10.3
* Android Emoji: Copyright &copy; [The Android Open Source Project](https://s3-eu-west-1.amazonaws.com/tw-font/android/NOTICE) - Lollipop
* Twitter Emoji Copyright &copy; Twitter, Inc. - The original release
* Emoji One Emoji: Copyright &copy; [Ranks.com Inc.](http://www.emojione.com/developers) - master as of 2015-03-05


## Using the data

The file you want is `emoji.json`. It contains an array of entries for emoji that 
look like this:

	[
		{
			"name": "WHITE UP POINTING INDEX",
			"unified": "261D",
			"variations": [
				"261D-FE0F"
			],
			"docomo": null,
			"au": "E4F6",
			"softbank": "E00F",
			"google": "FEB98",
			"image": "261d.png",
			"sheet_x": 1,
			"sheet_y": 2,
			"short_name": "point_up",
			"short_names": [
				"point_up"
			],
			"text": null,
			"texts": null,
			"category": "People",
			"sort_order": 116,
			"has_img_apple": true,
			"has_img_google": true,
			"has_img_twitter": true,
			"has_img_emojione": false,
			"skin_variations": {
				"261D-1F3FB": {
					"unified": "261D-1F3FB",
					"image": "261d-1f3fb.png",
					"sheet_x": 1,
					"sheet_y": 3,
					"has_img_apple": true,
					"has_img_google": false,
					"has_img_twitter": false,
					"has_img_emojione": false
				},
				...
			}
		},
		...
	]

An explanation of the various fields is in order:

* `name` - The offical Unicode name, in SHOUTY UPPERCASE.
* `unified` - The Unicode codepoint, as 4-5 hex digits. Where an emoji
   needs 2 or more codepoints, they are specified like `1F1EA-1F1F8`.
* `variations` - An array of commonly used codepoint variations.
* `docomo`, `au`, `softbank`, `google` - The Unicode codepoints used
   by various mobile vendors.
* `image` - The name of the image file.
* `sheet_x` & `sheet_y` - The position of the image in the spritesheets.
* `short_name` - The commonly-agreed upon short name for the image, as
   supported in campfire, github etc via the :colon-syntax:
* `short_names` - An array of all the known short names.
* `text` - An ASCII version of the emoji (e.g. `:)`), or null where
   none exists.
* `texts` - An array of ASCII emoji that should convert into this emoji.
   Each ASCII emoji will only appear against a single emoji entry.
* `has_img_*` - A flag for whether the given image set has an image (named by the `image` prop) available.
* `skin_variations` - For skin-varying emoji, a list of alternative glyphs.


## Rebuilding the data

The scripts in the `build/` sub directory allow you to rebuild the data files 
from the unicode.org source materials and the data from the gemoji project.

You can rebuild by following these steps:

	cd build/

	# Rebuild catalog.php from the original data tables
	wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
	patch < source_html.patch
	php build_catalog.php full.html > catalog.php

	# Rebuild the master mapping
	php build_names.php
	wget http://www.unicode.org/Public/UNIDATA/UnicodeData.txt
	php build_map.php

	# From the final mapping, build a preview table
	php build_table.php > ../table.htm

	# Rebuild positions and make the master spritesheets
	# (This step requires ImageMagick or GraphicsMagick)
	php build_image.php # this is slow!
	php build_sheets.php

To find out how to extract the original source glyphs, look inside the sub
directories under `build/`.
