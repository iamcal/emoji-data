#!/bin/bash

quant_sheet() {
	rm -f "$OUT"
	/usr/local/bin/pngquant $COLORS --nofs --skip-if-larger --output "$OUT" -- "$IN"

	STATUS=$?

	if [ $STATUS -eq 0 ]
	then
		IN_SIZE=$(get_size $IN)
		OUT_SIZE=$(get_size $OUT)

		echo "  Quantized $IN to $OUT"
		echo "  Old size : $IN_SIZE"
		echo "  New size : $OUT_SIZE"
	else
		if [ $STATUS -eq 99 ]
		then
			echo "  pngquant skipped due to low quality"
			cp "$IN" "$OUT"
		else
			if [ $STATUS -eq 98 ]
			then
				echo "  pngquant skipped due to poor compression"
				cp "$IN" "$OUT"
			else
				echo "  pngquant failed with status $STATUS"
				exit 1
			fi
		fi
	fi
}

get_size() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    echo $(stat -f%z "$1")
  else
    echo $(stat -c%s "$1")
  fi
}

for type in apple google twitter facebook messenger; do
	for size in 16 20 32 64; do
		for colors in 128 256; do
			echo "Quantizing ${type}_${size} to ${colors} colors"
			IN="../sheet_${type}_${size}.png"
			OUT="../sheets-indexed-${colors}/sheet_${type}_${size}_indexed_${colors}.png"
			COLORS=$colors
			quant_sheet;
		done
	done
done
