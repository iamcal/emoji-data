# Change Log

## 2018-07-05 : v4.1.0

* Removed EmojiOne at the request of EmojiOne/JoyPixels staff


## 2018-04-16 : v4.0.4

* Updated Emojione to v3.1.2 (thanks to @oriolbcn) https://github.com/iamcal/emoji-data/pull/132


## 2017-12-20 : v4.0.3

* Include aliased images from Google set https://github.com/iamcal/emoji-data/issues/120


## 2017-12-15 : v4.0.2

* Added `added_in` property for variations https://github.com/iamcal/emoji-data/pull/101
* Updated the preferred short codes for the following emoji to more natural CLDR shortnames:
  * Emoji v4: 1F91E, 1F936, 2695
  * Emoji v5: 1F928, 1F929, 1F92A, 1F92B, 1F92C, 1F92D, 1F92E, 1F92F


## 2017-12-07 : v4.0.1

* Included `categories.json` to show category order.
* Added missing skin modifiers to 9 emoji, with updated images https://github.com/iamcal/emoji-data/issues/105


## 2017-11-17 : v4.0.0

* Updated to Unicode 10 / Emoji 5
* Changed the unified codepoints to prefer the fully qualified version (with `-FE0F`), including the non-qualified version as an optional property
* Correctly handle skin-variation codepoints that have been obsoleted
* Added gaps between images in spritesheets to avoid bleed when displaying them zoomed - a 1px border around each image at all sizes
* Added real names for flags
* Added obsolete information for skin variations
* For obsolete emoji missing images, the sheets now use the image of the emoji it was obsoleted by
* Corrected softbank codepoints
* Better formating of `table.htm` to show which images are available


## 2017-05-08 : v3.0.0

* Since the npm packaging format has changed, increment the major version https://github.com/iamcal/emoji-data/issues/87


## 2017-05-06 : v2.5.2

* Fixed npm packaging


## 2017-05-05 : v.2.5.1

* Include all fallback images in every sheet, including skin tones. This allows sheet consumers to only include the sheets
  they want to use, without requiring the apple ones for fallback.


## 2017-05-04 : v2.5.0

* Upgraded maps to Unicode 9 / Emoji 4
* Made the kissing emoticons (e.g. :*) result in `kissing_heart` instead of `kiss` https://github.com/iamcal/emoji-data/pull/73
* Switched `U+1F373 COOKING` to use `:cooking:`/`:fried_egg:` as a short code (`:egg:` is now used by `U+1F95A EGG`)
* Added support for Facebook and FB Messenger images https://github.com/iamcal/emoji-data/pull/76
* Switched skin tone variations to be keyed by skintone codepoint, not full composite sequence.
* Added `obsoletes`/`obsoleted_by` fields.
* Added `added_in` field to show Unicode version source.


## 2016-11-11 : v2.4.5

* Vastly sped up image building https://github.com/iamcal/emoji-data/pull/60
* Updated to Android Nougat images https://github.com/iamcal/emoji-data/pull/68


## 2016-06-17 : v2.4.4

* Include categories for all codepoints
* Added bower.json
* Optimize all images using multiple tools
* Added indexed color sheets
* Updated Apple images to match 10.11.5:
  * Removed skin tones for U+1F3C7 (Horse Racing)
  * Added skin tones for U+1F575 (Sluth or Spy)
  * Shortened the height of U+1F579 (Joystick) so it has some headroom


## 2016-06-01 : v2.4.3

* Fixed shortnames for snowman (U+2603 & U+26C4)
* Added missing -FE0F variants for OS X/iOS
* Changed how we package for NPM


## 2016-05-12 : v2.4.2

* Changed :) to :slightly_smiling_face:
* Support for capitalized open_mouth emoticons
* Started processing the emoji 2.0 data files
* Added skin tones for U+1F575 (sluth/spy) - no Apple images yet
* Fixed the duplicate :umbrella: short names
* Added U+23CF (Eject symbol) - no Apple image yet


## 2016-04-20 : v2.4.1

* Fixed the duplicate :satellite: short names


## 2016-02-09 : v2.4.0

* Updated emojione images to the Q1 2016 release (v2.1.0)


## 2015-12-11 : v2.3.0

* Updated Google images to Android 6.0.1
* Updated Twitter images to include skin tones


## 2015-11-05 : v2.2.2

* Fixed :lightning: names
* Added :man-woman-boy: back as an alias


## 2015-11-04 : v2.2.1

* Fixed :scorpion: name
* Eemoved the duplicate family emoji


## 2015-10-22 : v2.2.0

* Updated to OS X 10.11.1 final, with lots of new emoji


## 2015-09-16 : v2.1.0

* Updated to OS X 10.11 beta, added new flags and spock emoji


## 2015-05-28 : v2.0.0

* First tagged version
* Tracking new iOS 8 skin-tone emoji
