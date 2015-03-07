# Fetching Android images

We extract Google glyphs from the TTF file that ships with Android:

* Download the latest version: https://android.googlesource.com/platform/external/noto-fonts/+/lollipop-release/NotoColorEmoji.ttf
* Run `perl extract.pl` (installing Font::TTF first)

Next you'll want to cut the 64px versions that are used in the sheets:

    php make64.php

And finally optimize them. This requires OptiPNG to be installed (http://optipng.sourceforge.net/)
and takes a very long time:

    optipng -o7 ../../img-google-64/*

The resulting 64px images are then ready to use.


## TTF chunk formats

* CBLC table: http://www.microsoft.com/typography/otspec170/cblc.htm
* CBDT table: http://www.microsoft.com/typography/otspec170/cbdt.htm
