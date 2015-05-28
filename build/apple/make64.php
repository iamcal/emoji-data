<?php
	# apple images are too big to composite at speed, so we'll make 64px
	# versions from the 160px source.
	#
	# we also transform the filenames to match our simple scheme of unified
	# codepoints as names.

	$files = glob("../../img-apple-160/*.png");

	$json = file_get_contents('../apple_extra.json');
	$obj = json_decode($json, true);
	$extra_images = $obj['images'];

	$skin_map = array(
		'.0.png'	=> '.png',
		'.1.png'	=> '-1f3fb.png',
		'.2.png'	=> '-1f3fc.png',
		'.3.png'	=> '-1f3fd.png',
		'.4.png'	=> '-1f3fe.png',
		'.5.png'	=> '-1f3ff.png',
	);

	foreach ($files as $src){

		#
		# figure out a destination path, based on the source path.
		# there are 3 types:
		# 
		# 1f3e0.png		-> 1f3e0.png		simple, no change
		# 1f3ca.1.png		-> 1f3ca-1f3fb.png	skin tone variations
		# 1f46a.0.mwb.png	-> 1f468-200d-1f469-200d-1f466	family glyphs
		#

		$bits = explode('/', $src);
		$final = array_pop($bits);
		#$orig = $final;

		foreach ($extra_images as $k => $v){
			if ($final == $v){
				$final = strtolower($k).'.png';
			}
		}

		$final = str_replace(array_keys($skin_map), $skin_map, $final);
		

		#if ($final != $orig) echo "$orig -> $final\n";
		#continue;

		$dst = '../../img-apple-64/'.$final;

		exec("convert {$src} -resize 64x64 png32:{$dst}", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo ".";
		}
	}

	echo "All done\n";
