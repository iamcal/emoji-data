#!/bin/bash

# parse some options!
STRIP=1
LEVEL=5

USE_PNGCRUSH=1
USE_OPTIPNG=1
USE_PNGOUT=0
USE_ZOPFLI=1
USE_ADVPNG=1

TMP=$(mktemp -d /tmp/optimize-XXXXXXXX)
if [ ! -d $TMP ]; then
	echo "Failed to create temp dir"
	exit 1
fi

if [ $LEVEL -lt 4 ] && [ $USE_ZOPFLI -eq 1 ]; then
	USE_PNGOUT=0
fi
if [ $LEVEL -lt 2 ] && [ $USE_OPTIPNG -eq 1 ]; then
	USE_PNGCRUSH=0
fi


check_file_size() {
	FILE_IS_SMALL=0
	FILE_IS_LARGE=0
	SIZE=$(get_size $IN)
	if [ $SIZE -gt "256000" ]; then
		FILE_IS_LARGE=1
	fi
	if [ $SIZE -lt "2048" ]; then
		FILE_IS_SMALL=1
	fi
}

# the logic for how to apply each program comes from ImageOptim, specifically
# the individual worker files. we use strip=yes and level=5 for most things.
# https://github.com/ImageOptim/ImageOptim/tree/master/imageoptim/Workers

pngcrush() {
	check_file_size

	CMD="pngcrush"
	if [ $FILE_IS_SMALL -eq 1 ] || ( [ $LEVEL -ge 6 ] && [ $FILE_IS_LARGE -eq 0 ] ) ; then
		CMD="$CMD -brute"
	fi
	if [ $STRIP -eq 1 ]; then
		CMD="$CMD -rem alla"
	fi
	CMD="$CMD -nofilecheck -bail -blacken -reduce \"$IN\" \"$OUT\""
}

optipng() {
	cp "$IN" "$OUT"
	OPTI=$(expr $LEVEL + 1)
	if [ $OPTI -gt 7 ]; then OPTI=7; fi
	if [ $OPTI -lt 3 ]; then OPTI=3; fi
	CMD="/usr/local/bin/optipng -o$OPTI -quiet \"$OUT\""
}

pngout() {
	check_file_size

	LVL=1
	if [ $LEVEL -ge 4 ]; then LVL=0; fi
	if [ $FILE_IS_LARGE -eq 1 ]; then
		LVL=$(expr $LVL + 1)
	fi

	CMD="pngout-static"
	if [ $STRIP -eq 0 ]; then
		CMD="$CMD -k1"
	fi
	if [ $LVL -gt 0 ]; then
		CMD="$CMD -s$LVL"
	fi
	CMD="$CMD -r -q \"$IN\" \"$OUT\""
}

advpng() {
	LVL=$LEVEL
	if [ $LVL -gt 4 ]; then LVL=4; fi
	if [ $LVL -lt 1 ]; then LVL=1; fi

	cp "$IN" "$OUT"
	CMD="advpng -$LVL --recompress --quiet \"$OUT\""
}

zopfli() {
	check_file_size

	FILTERS="--filters=0pme"
	ALTERNATIVE_STRAT=0
	SIZE=$(get_size $IN)

	LIMIT_MUL="0.8"
	if [ $ALTERNATIVE_STRAT -eq 1 ]; then LIMIT_MUL="1.4"; fi
	TIMELIMIT=$(php -r "echo round(min(8 + $LEVEL * 13, 10 + $SIZE / 2014) * $LIMIT_MUL);")

	if [ $FILE_IS_LARGE -eq 1 ]; then
		ITERATIONS=$(php -r "echo round(5 + (3 + 3 * $LEVEL) / 3);")
		FILTERS="--filters=p"
	else
		ITERATIONS=$(php -r "echo (3 + 3 * $LEVEL);")
	fi

	if [ $ALTERNATIVE_STRAT -eq 1 ]; then
		FILTERS="--filters=bp"
	fi

	#CMD="/usr/local/bin/zopflipng --timelimit=$TIMELIMIT"
	CMD="/usr/local/bin/zopflipng"
	if [ $ITERATIONS -gt 0 ]; then
		CMD="$CMD --iterations=$ITERATIONS"
	fi
	CMD="$CMD $FILTERS"
	if [ $STRIP -eq 0 ]; then
		CMD="$CMD --keepchunks=tEXt,zTXt,iTXt,gAMA,sRGB,iCCP,bKGD,pHYs,sBIT,tIME,oFFs,acTL,fcTL,fdAT,prVW,mkBF,mkTS,mkBS,mkBT"
	fi

	CMD="$CMD --lossy_transparent -y \"$IN\" \"$OUT\" > /dev/null"
}



show_size() {
	SIZE=$(get_size $OUT)
	echo "  $LABEL $SIZE"
}

execute_step() {
	if [ $FLAG -eq 1 ]; then

		rm -f "$OUT"
		#echo " -- $CMD"
		#echo " -- $IN -> $OUT"
		eval $CMD
		LAST_STATUS=$?
		if [ ! -f "$OUT" ]; then
			#echo "no output found at $OUT, copying $IN";
			cp "$IN" "$OUT"
		fi
		show_size
		#echo "file at $OUT"
	else
		rm -f "$OUT"
		cp "$IN" "$OUT"
		#echo "  $LABEL (skip)"
	fi
}

get_size() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    echo $(stat -f%z "$1")
  else
    echo $(stat -c%s "$1")
  fi
}

for f in $*
do
	echo "$f"

	IN=$f
	OUT=$f
	LABEL="start   "
	show_size

	IN=$OUT
	OUT="$TMP/step1.png"
	LABEL="pngcrush"
	FLAG=$USE_PNGCRUSH
	pngcrush
	execute_step

	IN=$OUT
	OUT="$TMP/step2.png"
	LABEL="optipng "
	FLAG=$USE_OPTIPNG
	optipng
	execute_step

	IN=$OUT
	OUT="$TMP/step3.png"
	LABEL="pngout  "
	FLAG=$USE_PNGOUT
	pngout
	execute_step

	IN=$OUT
	OUT="$TMP/step4.png"
	LABEL="advpng  "
	FLAG=$USE_ADVPNG
	advpng
	execute_step

	IN=$OUT
	OUT="$TMP/step5.png"
	LABEL="zopfli  "
	FLAG=$USE_ZOPFLI
	zopfli
	execute_step

	rm -f "$f"
	cp "$TMP/step5.png" "$f"
	rm -f "$TMP/step5.png"
done

rm -rf "$TMP/"
