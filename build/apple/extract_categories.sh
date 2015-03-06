#!/bin/bash
plist="/System/Library/Input Methods/CharacterPalette.app/Contents/Resources/Category-Emoji.plist"
outfile=../emoji_categoires.json
plutil -convert json -r "$plist" -o "$outfile"
