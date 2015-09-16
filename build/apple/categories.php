<?php
	$obj = json_decode(file_get_contents('../emoji_categories.json'), true);

foreach ($obj['EmojiDataArray'] as $group){

	echo "{$group['CVDataTitle']}:\n";

	$s = $group['CVCategoryData']['Data'];
	$a = explode(',', $s);

	foreach ($a as $e){

		$bytes = array();
		for ($i=0; $i<strlen($e); $i++){

			$bytes[] = ord(substr($e, $i, 1));
		}

		$cps = array();
		while (count($bytes)){

			$first = array_shift($bytes);
			#echo "first byte: $first\n";

			if (($first & 0x80) == 0){
				$cps[] = $first;
				continue;
			}

			if (($first & 0xE0) == 0xC0){
				$second = array_shift($bytes);
				$cps[] = (($first & 0x1F) << 6) | ($second & 0x3F);
				continue;
			}

			if (($first & 0xF0) == 0xE0){
				$second = array_shift($bytes);
				$third = array_shift($bytes);
				$cps[] = (($first & 0xF) << 12) | (($second & 0x3F) << 6) | ($third & 0x3F);
				continue;
			}

			if (($first & 0xF8) == 0xF0){
				$second = array_shift($bytes);
				$third = array_shift($bytes);
				$fourth = array_shift($bytes);
				$cps[] = (($first & 0x7) << 18) | (($second & 0x3F) << 12) | (($third & 0x3F) << 6) | ($fourth & 0x3F);
				continue;
			}

			echo "    unhandled point $first\n";
		}

		$encoded = array();
		foreach ($cps as $cp){
			$encoded[] = sprintf('%04x', $cp);
		}

		echo '    '.implode(', ', $encoded)."\n";
	}
}
