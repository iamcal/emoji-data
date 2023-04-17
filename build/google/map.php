<?php
	$src = "noto-emoji/png/128";
	$src_flags = "noto-emoji/build/resized_flags";
	$dst = "../../img-google-136";

	$flag_map = [
		'us' => 'US',
		'cn' => 'CN',
		'de' => 'DE',
		'es' => 'ES',
		'fr' => 'FR',
		'gb' => 'GB',
		'it' => 'IT',
		'jp' => 'JP',
		'kr' => 'KR',
		'ru' => 'RU',
		'us' => 'US',
		'flag-england'	=> 'GB-ENG',
		'flag-wales'	=> 'GB-WLS',

		// from noto-emoji/emoji_aliases.txt
		'flag-bv' => 'NO',
		'flag-cp' => 'FR',
		'flag-dg' => 'IO',
		'flag-ea' => 'ES',
		'flag-hm' => 'AU',
		'flag-mf' => 'FR',
		'flag-sj' => 'NO',
		'flag-um' => 'US',
	];

	# remove existing
	shell_exec("rm -f $dst/*.png");

	# load alias map
	$aliases = array();
	$lines = file('noto-emoji/emoji_aliases.txt');
	foreach ($lines as $line){
		if (strpos($line, '#') !== false){
			list($line, $junk) = explode('#', $line);
		}
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

	echo "\nAll done\n";

	function try_fetch($row){

		global $src, $src_flags, $dst, $aliases, $flag_map;

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


		#
		# flags come from elsewhere...
		#

		if (preg_match('!flag-(..)!', $row['short_name'], $m)){

			$cc = StrToUpper($m[1]);

			if (file_exists("{$src_flags}/{$cc}.png")){
				copy("{$src_flags}/{$cc}.png", "$dst/$out");
				echo ',';
				return;
			}
		}

		if (isset($flag_map[$row['short_name']])){
			$cc = $flag_map[$row['short_name']];

			if (file_exists("{$src_flags}/{$cc}.png")){
				copy("{$src_flags}/{$cc}.png", "$dst/$out");
				echo ':';
				return;
			}
		}


		echo 'X';
		echo "({$row['short_name']})";
	}
