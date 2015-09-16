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
