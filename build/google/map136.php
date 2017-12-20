<?php
	$src = "noto-emoji/build/compressed_pngs";
	$dst = "../../img-google-136";

	# remove existing
	shell_exec("rm -f $dst/*.png");

	# load alias map
	$aliases = array();
	$lines = file('noto-emoji/emoji_aliases.txt');
	foreach ($lines as $line){
		list($line, $junk) = explode('#', $line);
		$line = trim($line);
		if (strlen($line)){
			list($from, $to) = explode(';', $line);
			$aliases[$from] = $to;
		}
	}

	# load catalog
	$json = file_get_contents('../../emoji.json');
	$emoji = json_decode($json, true);

	foreach ($emoji as $row){
		try_fetch($row);
		if (isset($row['skin_variations'])){
			foreach ($row['skin_variations'] as $row2){
				try_fetch($row2);
			}
		}
	}

	function try_fetch($row){

		global $src, $dst, $aliases;

		$out = $row['image'];


		#
		# generate a list of image paths to try
		#

		$roots = array();

		$roots[] = str_replace('-', '_', StrToLower($row['unified']));

		if (isset($row['non_qualified']) && $row['non_qualified']){
			$roots[] = str_replace('-', '_', StrToLower($row['non_qualified']));
		}else{
			if (strpos($row['image'], '-fe0f')){
				$roots[] = str_replace('-', '_', str_replace('-fe0f', '', StrToLower($row['unified'])));
			}
		}

		foreach ($roots as $root){
			if (isset($aliases[$root])){
				$roots[] = $aliases[$root];
			}
		}


		#
		# find the first one that exists
		#

		foreach ($roots as $root){

			$inx = 'emoji_u'.$root.'.png';
			if (file_exists("$src/$inx")){
				copy("$src/$inx", "$dst/$out");
				echo '.';
				return;
			}
		}

		echo 'X';
	}
