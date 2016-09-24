#!/usr/bin/env bash
# make136.sh - create 136px emoji images for google

set -e

# cd to project root
cd "$(dirname -- "$0")/../.."

noto_emoji='build/google/noto-emoji'

# Build noto-emoji
cd "$noto_emoji"
make -j "$(nproc)" compressed
cd -

src="$noto_emoji"'/build/compressed_pngs'
dst='img-google-136'

rm -rf "$dst"
mkdir -p "$dst"

for f_src in "$src"/emoji_u*.png; do
	f_dst="${f_src##*/emoji_u}"
	f_dst="$dst/${f_dst//'_'/'-'}"
	cp "$f_src" "$f_dst"
done
