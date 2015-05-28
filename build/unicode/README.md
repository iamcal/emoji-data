# Skin swatch images

Currently no emoji image sets have a stand-alone emoji for skin tone patches.
However, the Unicode technical report that introduced skine tone modifiers
([TR #51](http://unicode.org/reports/tr51/#Emoji_Variation_Sequences)) has
some images we can use. The script in this directory just takes the reference
images and then cuts them down to size for us to use. Easy.

	wget http://unicode.org/reports/tr51/images/other/swatch-type-1-2.png
	wget http://unicode.org/reports/tr51/images/other/swatch-type-3.png
	wget http://unicode.org/reports/tr51/images/other/swatch-type-4.png
	wget http://unicode.org/reports/tr51/images/other/swatch-type-5.png
	wget http://unicode.org/reports/tr51/images/other/swatch-type-6.png
	php make64.php
