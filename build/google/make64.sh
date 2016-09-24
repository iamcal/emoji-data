#!/usr/bin/env bash
# make64.sh - create 64px emoji images for google

set -e

# cd to project root
cd "$(dirname -- "$0")/../.."

src='img-google-136'
dst='img-google-64'

rm -rf "$dst"
mkdir -p "$dst"

mogrify -gravity center -background transparent -extent '64x64' \
	-path "png32:$dst" "$src"'/*.png[64x64]'
