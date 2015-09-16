<?php
	#
	# this script builds a list of all the flags we have images for
	#

	$files = glob("../img-apple-64/1f1*-1f1*.png");

	$cps = array();
	foreach ($files as $file){
		$bits = explode('/', $file);
		$bits = explode('.', array_pop($bits));
		$cp = StrToUpper(array_shift($bits));
		$cps[] = $cp;
	}

	sort($cps);

	foreach ($cps as $cp){

		if (!strlen($cp)) continue;

		list($a, $b) = explode('-', $cp);
		$a = hexdec($a) - 0x1F1E6;
		$b = hexdec($b) - 0x1F1E6;

		$a = chr(ord('a') + $a);
		$b = chr(ord('a') + $b);

		echo "$cp;flag-$a$b\n";
	}
