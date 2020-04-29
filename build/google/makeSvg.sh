#!/usr/bin/env bash
# makeSvg.sh - create SVG emoji images for google

set -e

# cd to project root
cd "$(dirname -- "$0")/../.."

src='build/google/noto-emoji/svg'
dst='svg-google'

rm -rf "$dst"
mkdir -p "$dst"

for f_src in "$src"/emoji_u*.svg; do
	f_dst="${f_src##*/emoji_u}"
	f_dst="$dst/${f_dst//'_'/-}"
	cp "$f_src" "$f_dst"
done
