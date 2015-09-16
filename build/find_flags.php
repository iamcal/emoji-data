<?php
	#
	# this script builds a list of all the flags we have images for
	#

	$a = find_flags("../img-apple-64/1f1*-1f1*.png");
	$b = find_flags("../img-google-64/1f1*-1f1*.png");
	$c = find_flags("../img-twitter-64/1f1*-1f1*.png");
	$d = find_flags("../img-emojione-64/1f1*-1f1*.png");

	$all = array_unique(array_merge($a, $b, $c, $d));
	sort($all);

	foreach ($all as $line) echo $line;

	function find_flags($path){

		$files = glob($path);

		$cps = array();
		foreach ($files as $file){
			$bits = explode('/', $file);
			$bits = explode('.', array_pop($bits));
			$cp = StrToUpper(array_shift($bits));
			$cps[] = $cp;
		}

		$out = array();

		foreach ($cps as $cp){

			if (!strlen($cp)) continue;

			list($a, $b) = explode('-', $cp);
			$a = hexdec($a) - 0x1F1E6;
			$b = hexdec($b) - 0x1F1E6;

			$a = chr(ord('a') + $a);
			$b = chr(ord('a') + $b);

			$out[] = "$cp;flag-$a$b\n";
		}

		return $out;
	}
