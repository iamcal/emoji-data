<?php
	include('catalog.php');
	include('catalog_positions.php');
	include('catalog_names.php');

	# load text mappings
	$lines = file('catalog_text.txt');
	$text = array();
	foreach ($lines as $line){
		$line = trim($line);
		if (strlen($line)){
			$bits = preg_split('!\s+!', $line, 2);
			$text[$bits[0]] = $bits[1];
		}
	}

	# pre-process names into a map
	$names_map = array();
	foreach ($catalog_names as $row){
		if ($row[0] && $row[1]){
			$names_map[$row[0]] = $row[1];
		}
	}

	$out = array();

	foreach ($catalog as $row){

		$img_key = StrToLower(encode_points($row['unicode']));

		# for some reason, gemoji names 0023-20e3 as just 0023
		if (preg_match('!^(\S{4})-20e3$!', $img_key, $m)) $img_key = $m[1];

		$position = $position_data[$img_key];
		$short = $names_map[$img_key];

		if (!is_array($position)){
			echo "No image for $img_key: {$row['char_name']['title']}\n";
			continue;
		}


		$out[] = array(
			'name'		=> $row['char_name']['title'],
			'unified'	=> encode_points($row['unicode']),
			'docomo'	=> encode_points($row['docomo'  ]['unicode']),
			'au'		=> encode_points($row['au'      ]['unicode']),
			'softbank'	=> encode_points($row['softbank']['unicode']),
			'google'	=> encode_points($row['google'  ]['unicode']),
			'image'		=> $img_key.'.png',
			'sheet_x'	=> $position['x'],
			'sheet_y'	=> $position['y'],
			'short_name'	=> $short,
			'text'		=> $text[$short],
		);
	}

	$fh = fopen('../emoji.json', 'w');
	fwrite($fh, json_encode($out));
	fclose($fh);
	echo "DONE\n";


	function encode_points($points){
		$bits = array();
		if (is_array($points)){
			foreach ($points as $p){
				$bits[] = sprintf('%04X', $p);
			}
		}
		return implode('-', $bits);
	}
