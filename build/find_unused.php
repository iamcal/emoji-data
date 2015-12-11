<?php
	#
	# first, build a list of every image referenced in the final JSON
	#

	$json = file_get_contents('../emoji.json');
	$obj = json_decode($json, true);

	$files = array();
	foreach ($obj as $row){

		$files[$row['image']]++;

		if (is_array($row['skin_variations'])){
			foreach ($row['skin_variations'] as $var){
				$files[$var['image']]++;
			}
		}
	}


	#
	# now build a list of every dumb codepoint we never care about
	#

	$skip = array();

	$skip[] = "0023.png"; # hash
	$skip[] = "002a.png"; # star
	foreach (range(0x30, 0x39) as $a) $skip[] = sprintf('%04x.png', $a); # digits 0-9
	foreach (range(0x1f1e6, 0x1f1ff) as $a) $skip[] = sprintf('%04x.png', $a); # country codes A-Z

	foreach ($skip as $filename) $files[$filename]++;


	#
	# now find unused ones
	#

	scan_unused($files, '../img-apple-64/');
	scan_unused($files, '../img-google-64/');
	scan_unused($files, '../img-twitter-64/');
	scan_unused($files, '../img-emojione-64/');
	echo "~FIN~\n";

	function scan_unused($files, $path){

		$found = glob("$path*.png");
		foreach ($found as $test){
			$bits = explode('/', $test);
			$last = array_pop($bits);

			if (!$files[$last]){

				echo "unused file: $path -- $last\n";

			}
		}
	}
