#!/bin/bash

for f in $*
do

	echo "Processing $f";

	rm -f /tmp/stage1.png /tmp/stage2.png

	ORIG_SIZE=$(stat -c%s "$f")
	echo "  start    $ORIG_SIZE"

	pngcrush -brute -reduce -s $f /tmp/stage1.png
	SIZE2=$(stat -c%s "/tmp/stage1.png")
	echo "  pngcrush $SIZE2"

	optipng -o7 /tmp/stage1.png > /dev/null
	SIZE3=$(stat -c%s "/tmp/stage1.png")
	echo "  optipng  $SIZE3"

	pngout-static -q /tmp/stage1.png /tmp/stage2.png
	SIZE4=$(stat -c%s "/tmp/stage2.png")
	echo "  pngout   $SIZE4"

	advpng --recompress --shrink-insane --quiet /tmp/stage2.png
	SIZE5=$(stat -c%s "/tmp/stage2.png")
	echo "  advpng   $SIZE5"

	node ~/zopfli-png/zopfli-png.js --i1000 --silent /tmp/stage2.png
	SIZE6=$(stat -c%s "/tmp/stage2.png")
	echo "  zopfli   $SIZE6"

	rm -f "$f"
	cp /tmp/stage2.png "$f"
done
