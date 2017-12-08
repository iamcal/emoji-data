# emoji-data - Easy to consume Emoji data and images

[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fiamcal%2Femoji-data.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fiamcal%2Femoji-data?ref=badge_shield)

This project provides easy-to-parse data about emoji, along with a spritesheet-style 
images for use on the web.

You can see a catalog of the emoji data here: http://unicodey.com/emoji-data/table.htm


## Installation

The git repo is pretty big (almost 4GB), but contains everything. If you want to use `npm`, you can:

    npm install emoji-datasource

This will only install the 32px full-fidelity spritesheets. If you want different size sheets (16,20 or 64px),
quantized sheets (128 or 256 color) or the individual images (at 64px) then you'll need to install additional
npm modules:
```bash
npm install emoji-datasource-apple
npm install emoji-datasource-google
npm install emoji-datasource-twitter
npm install emoji-datasource-emojione
npm install emoji-datasource-facebook
npm install emoji-datasource-messenger
```

## Using the data

The file you want is `emoji.json`. It contains an array of entries for emoji that 
look like this:

```json
[
    {
        "name": "WHITE UP POINTING INDEX",
        "unified": "261D-FE0F",
        "non_qualified": "261D",
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
        "added_in": "1.4",
        "has_img_apple": true,
        "has_img_google": true,
        "has_img_twitter": true,
        "has_img_emojione": false,
        "has_img_facebook": false,
        "has_img_messenger": false,
        "skin_variations": {
            "1F3FB": {
                "unified": "261D-1F3FB",
                "image": "261d-1f3fb.png",
                "sheet_x": 1,
                "sheet_y": 3,
                "added_in": "6.0",
                "has_img_apple": true,
                "has_img_google": false,
                "has_img_twitter": false,
                "has_img_emojione": false,
                "has_img_facebook": false,
                "has_img_messenger": false,
                ...
            }
        },
        "obsoletes": "ABCD-1234",
        "obsoleted_by": "5678-90EF"
    },
    ...
]
```

An explanation of the various fields is in order:

<table>
    <thead>
        <tr>
            <th>Field</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="center">name</td>
            <td>The offical Unicode name, in SHOUTY UPPERCASE.</td>
        </tr>
        <tr>
            <td align="center">unified</td>
            <td>The Unicode codepoint, as 4-5 hex digits. Where an emoji needs 2 or more codepoints, they are specified like
                1F1EA-1F1F8. For emoji that need to specifiy a variation selector (-FE0F), that is included here.</td>
        </tr>
        <tr>
            <td align="center">non_qualified</td>
            <td>For emoji that also have usage without a variation selector, that version is included here (otherwise is null).</td>
        </tr>
        <tr>
            <td align="center">docomo, u, softbank, google</td>
            <td>The legacy Unicode codepoints used by various mobile vendors.</td>
        </tr>
        <tr>
            <td align="center">image</td>
            <td>The name of the image file.</td>
        </tr>
        <tr>
            <td align="center">sheet_x & sheet_y</td>
            <td>The position of the image in the spritesheets.</td>
        </tr>
        <tr>
            <td align="center">short_name</td>
            <td>The commonly-agreed upon short name for the image, as supported in campfire, github etc via the :colon-syntax:</td>
        </tr>
        <tr>
            <td align="center">short_names</td>
            <td>An array of all the known short names.</td>
        </tr>
        <tr>
            <td align="center">text</td>
            <td>An ASCII version of the emoji (e.g. :)), or null where none exists.</td>
        </tr>
        <tr>
            <td align="center">texts</td>
            <td>An array of ASCII emoji that should convert into this emoji. Each ASCII emoji will only appear against a single
                emoji entry.
        </tr>
        <tr>
            <td align="center">has_img_</td>
            <td>A flag for whether the given image set has an image (named by the image prop) available.</td>
        </tr>
        <tr>
            <td align="center">added_id</td>
            <td>Unicode versions in which this codepoint/sequence was added.</td>
        </tr>
        <tr>
            <td align="center">skin_variations</td>
            <td>For skin-varying emoji, a list of alternative glyphs, keyed by the skin tone.</td>
        </tr>
        <tr>
            <td align="center">obsoletes / obsoleted_by</td>
            <td>Emoji that are no longer used, in preference of gendered versions.</td>
        </tr>
    </tbody>
</table>


## Version history

See [CHANGES.md](CHANGES.md)


## Image Sources

Images are extracted from their sources and this library attempts to track the latest
available versions. If you're looking for older versions of Apple or Android images
(such as the Hairy Heart) then you'll need to look at previous revisions.

* Apple Emoji: Copyright &copy; Apple Inc. - macOS 10.13 (High Sierra)
* Android Emoji: Copyright &copy; [The Android Open Source Project](https://s3-eu-west-1.amazonaws.com/tw-font/android/NOTICE) - 11275b5 / 2017-10-30
* Twitter Emoji: Copyright &copy; Twitter, Inc. - v2.3.1 2017-10-31
* Emoji One Emoji: Copyright &copy; [Ranks.com Inc.](http://www.emojione.com/developers) - v2.2.7 2016-12-02
* Facebook/Messenger Emoji: Copyright &copy; Facebook, Inc. - v7, fetched 2017-11-15


## Libraries which use this data

* https://github.com/iamcal/js-emoji - JavaScript emoji library
* https://github.com/iamcal/php-emoji - PHP emoji library
* https://github.com/mroth/emoji-data-js - NodeJS emoji library
* https://github.com/mroth/emoji_data.rb - Ruby emoji library
* https://github.com/mroth/exmoji - Elixir/Erlang emoji library
* https://github.com/needim/wdt-emoji-bundle - a Slack-style JavaScript emoji picker
* https://github.com/mroth/emojistatic - emoji image CDN
* https://github.com/juanfran/emoji-data-css - emoji css files
* https://github.com/afeld/emoji-css/ - an easy way to include emoji in your HTML
