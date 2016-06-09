#!/bin/bash

TMP=$(mktemp -d /tmp/optimize-XXXXXXXX)
if [ ! -d $TMP ]; then
	echo "Failed to create temp dir"
	exit 1
fi
TEMP1="$TMP/stage1.png"
TEMP2="$TMP/stage2.png"

for f in $*
do

	rm -f "$TEMP1" "$TEMP2"

	echo "Processing $f";

	ORIG_SIZE=$(stat -c%s "$f")
	echo "  start    $ORIG_SIZE"

	pngcrush -brute -reduce -noforce -s $f $TEMP1
	if [ ! -f $TEMP1 ]; then
		cp $f $TEMP1
	fi
	SIZE2=$(stat -c%s "$TEMP1")
	echo "  pngcrush $SIZE2"

	/usr/local/bin/optipng -o7 -zm1-9 -quiet $TEMP1
	SIZE3=$(stat -c%s "$TEMP1")
	echo "  optipng  $SIZE3"

	pngout-static -q $TEMP1 $TEMP2
	SIZE4=$(stat -c%s $TEMP2)
	echo "  pngout   $SIZE4"

	advpng --recompress --shrink-insane --quiet $TEMP2
	SIZE5=$(stat -c%s $TEMP2)
	echo "  advpng   $SIZE5"

	node ~/zopfli-png/zopfli-png.js --i1000 --silent $TEMP2
	SIZE6=$(stat -c%s $TEMP2)
	echo "  zopfli   $SIZE6"

	rm -f "$f"
	cp $TEMP2 "$f"
done
